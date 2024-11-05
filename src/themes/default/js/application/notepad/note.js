'use strict';

class Application_Notepad_Note
{
    /**
     * @param {Application_Notepad} notepad
     * @param {Number} id
     */
    constructor(notepad, id)
    {
        this.ERROR_CANNOT_DELETE_NOTE = 90301;
        this.ERROR_CANNOT_PIN_NOTE = 90302;

        this.notepad = notepad;
        this.id = id;
        this.loaded = false;
        this.loading = false;
        this.rendering = false;
        this.deleting = false;
        this.elContainer = null;
        this.elTitle = null;
        this.elTitleText = null;
        this.elBody = null;
        this.elLoader = null;
        this.editMode = false;
        this.logger = new Logger(sprintf('Notepad | Note [%s]', id));
        this.resizeSensor = null;

        this.elTitleInput = null;
        this.elTextInput = null;

        this.CreateContainer();
        this.Refresh();
    }

    Refresh()
    {
        if(this.editMode || this.deleting || this.rendering)
        {
            return;
        }

        if(!this.loading)
        {
            this.logger.logEvent('Refresh');

            this.LoadData();
        }
        else
        {
            this.logger.logEvent('Refresh | Ignoring, still loading data.');
        }
    }

    /**
     * @returns {Number}
     */
    GetID()
    {
        return this.id;
    }

    LoadData()
    {
        this.logger.logEvent('LoadData');

        this.loading = true;

        const notepad = this;
        let payload = {
            'note_id': this.id
        };

        application.createAJAX('NotepadGet')
            .SetPayload(payload)
            .Success(function(data) {
                notepad.Handle_DataLoaded(data);
            })
            .Failure(function () {
                notepad.Handle_DataNotFound();
            })
            .Always(function() {
                notepad.loading = false;
            })
            .Send();
    }

    Handle_DataNotFound()
    {
        this.Remove();
    }

    Handle_DataLoaded(data)
    {
        this.logger.logEvent('DataLoaded');
        this.logger.logData(data);

        this.data = data;

        this.Render();
    }

    Handle_DeleteSuccess()
    {
        this.logger.logEvent('DeleteSuccess');

        this.Remove();
    }

    Remove()
    {
        this.logger.logUI('Remove DOM elements.');

        this.elContainer.remove();
        this.notepad.Handle_NoteRemoved(this);
    }

    IsReady()
    {
        return !this.loading && !this.deleting && !this.rendering;
    }

    ShowLoader()
    {
        this.elTitle.hide();
        this.elBody.hide();
        this.elLoader.show();
    }

    DialogDelete()
    {
        if(confirm(t('This will delete the note.')+'\n'+t('This cannot be undone, are you sure?')) === true) {
            this.Delete();
        }
    }

    Delete()
    {
        if(this.deleting)
        {
            return;
        }

        this.logger.log('Delete | Requested to delete the note.');

        this.deleting = true;

        this.ShowLoader();

        var payload = {
            'note_id': this.id
        };

        var note = this;

        application.createAJAX('NotepadDelete')
            .SetPayload(payload)
            .Success(function() {
                note.Handle_DeleteSuccess();
            })
            .Error(t('Could not delete the note in the server.'), this.ERROR_CANNOT_DELETE_NOTE)
            .Send()
    }

    Edit()
    {
        if(this.editMode)
        {
            return;
        }

        this.logger.log('Edit | Entering edit mode.');

        this.editMode = true;

        this.RenderEditForm();

        this.elTextInput.focus();
    }

    ConfirmEdits()
    {
        this.logger.log('Edit | Saving the changes.');

        this.elContainer.removeClass('note-editing');

        var title = this.elTitleInput.val();
        var content = this.elTextInput.val();

        this.ShowLoader();

        var payload = {
            'note_id': this.id,
            'title': title,
            'content': content
        };

        const note = this;

        application.createAJAX('NotepadSave')
            .SetPayload(payload)
            .Success(function (data) {
                note.Handle_SaveSuccess(data);
            })
            .Send();
    }

    Handle_SaveSuccess(data)
    {
        this.logger.logEvent('SaveSuccess');

        this.editMode = false;

        this.Handle_DataLoaded(data);
    }

    CancelEdits()
    {
        this.editMode = false;

        this.elContainer.removeClass('note-editing');

        this.Render();
    }

    RenderEditForm()
    {
        const note = this;

        this.elContainer.addClass('note-editing');

        this.elTitleInput = $('<input type="text"/>')
            .addClass('form-control')
            .addClass('notepad-note-input-title')
            .attr('placeholder', t('Enter a title here...'))
            .val(this.data.title);

        this.elTitleText.html(this.elTitleInput);

        var rows = 3;
        var maxRows = 14;

        var amountLines = this.data.content.split('\n').length;
        if(amountLines > rows) {
            rows = amountLines;
        }

        if(amountLines > maxRows) {
            rows = maxRows;
        }

        this.elTextInput = $('<textarea/>')
            .addClass('form-control')
            .addClass('notepad-note-input-body')
            .attr('placeholder', t('Enter your notes here...'))
            .attr('rows', rows)
            .val(this.data.content);

        this.elBody.html('');

        this.elBody.append($('<p/>')
            .addClass('notepad-note-text-para')
            .append(this.elTextInput)
        );

        this.elBody.append($('<p/>')
            .addClass('notepad-note-hints')
            .append(
                t(
                    'You may use %1$s syntax for text formatting.',
                    '<a href="https://www.markdownguide.org/basic-syntax/" target="_blank">Markdown</a>'
                ) +
                ' ' +
                t('HTML is not allowed.')
            )
        );

        this.elBody.append(
            $('<p/>')
                .addClass('notepad-note-buttons')
                .append(
                    UI.Button(t('OK'))
                        .MakeSmall()
                        .MakePrimary()
                        .SetIcon(UI.Icon().OK())
                        .Click(function () {
                            note.ConfirmEdits();
                        })
                        .Render()
                )
                .append(' ')
                .append(
                    UI.Button(t('Cancel'))
                        .MakeSmall()
                        .Click(function () {
                            note.CancelEdits();
                        })
                        .Render()
                )
        );
    }

    PinToQuickstart()
    {
        this.logger.log('PinToQuickstart | Pinning the note to the quickstart screen.');

        var note = this;
        var payload ={
            'note_id': this.id
        };

        application.createAJAX('NotepadPin')
            .SetPayload(payload)
            .Error(t('Could not pin the note, the request failed.'), this.ERROR_CANNOT_PIN_NOTE)
            .Success(function() {
                note.Handle_PinSuccess();
            })
            .Send();
    }

    Handle_PinSuccess()
    {
        this.logger.logEvent('PinSuccess | Successfully pinned the note.');

        var params = new URLSearchParams(window.location.search);
        var dialog;

        // Different message when we are already in the welcome screen.
        if(params.has('page') && params.get('page') === 'welcome')
        {
            dialog = application.createDialogMessage(
                '<p>'+
                    '<strong>' + t('The note has been pinned.') + '</strong>' +
                '</p>' +
                '<p>'+
                    t('It will appear the next time you reload the page.') +
                '</p>'
            );
        }
        else {
            dialog = application.createConfirmationDialog(
                '<p>' +
                    '<strong>' + t('The note has been pinned to your quickstart screen.') + '</strong>' +
                '</p>' +
                '<p>' +
                    t('Do you want to go there now?') +
                '</p>'
            )
                .OK(function () {
                    application.redirect(
                        {
                            'page': 'welcome'
                        }
                    )
                });
        }

        dialog
            .SetIcon(UI.Icon().Notepad())
            .SetTitle(t('Notepad'))
            .Show();
    }

    CreateContainer()
    {
        this.logger.logUI('Creating the note container.');

        const note = this;

        this.elContainer = $('<div/>')
            .addClass('notepad-note')
            .attr('data-note-id', this.id);

        this.elTitleText = $('<span/>')
            .addClass('notepad-note-title-text');

        var toolbar = $('<div>')
            .addClass('notepad-note-icons')
            .append(
                UI.Icon().Edit()
                    .AddClass('notepad-note-icon')
                    .AddClass('note-icon-edit')
                    .MakeInformation()
                    .SetTitle(t('Edit this note.'))
                    .Click(function () {
                        note.Edit();
                    })
                    .Render()
            )
            .append(
                UI.Icon().Pin()
                    .AddClass('notepad-note-icon')
                    .AddClass('note-icon-pin')
                    .MakeInformation()
                    .SetTitle(t('Pin this note to your quickstart screen.'))
                    .Click(function () {
                        note.PinToQuickstart();
                    })
                    .Render()
            )
            .append(
                UI.Icon().DeleteSign()
                    .AddClass('notepad-note-icon')
                    .AddClass('note-icon-delete')
                    .MakeDangerous()
                    .SetTitle(t('Delete this note.'))
                    .Click(function () {
                        note.DialogDelete();
                    })
                    .Render()
            );

        this.elTitle = $('<div/>')
            .addClass('notepad-note-title')
            .append(toolbar)
            .append(this.elTitleText)
            .hide();

        this.elBody = $('<div/>')
            .addClass('notepad-note-body')
            .hide();

        this.elLoader = $('<div/>')
            .addClass('notepad-note-loader')
            .html(application.renderSpinner()+' '+t('Loading...'));

        this.elContainer.append(this.elLoader);
        this.elContainer.append(this.elTitle);
        this.elContainer.append(this.elBody);

        this.notepad.AppendNoteElement(this.elContainer);

        // Observe the note container element being
        // resized, to automatically update the layout.
        this.resizeSensor = new ResizeSensor(this.elContainer, function () {
            note.UpdateLayout('Note element has been resized.');
        });
    }

    Render()
    {
        if(this.rendering) {
            return;
        }

        this.rendering = true;

        this.logger.logUI('Rendering the note.');

        const note = this;
        let content = this.data.html;

        if(content.length === 0)
        {
            content = $('<span class="notepad-note-placeholder">'+t('Click to write some notes...')+'</span>');
            content = $(content)
                .css('cursor', 'pointer')
                .click(function () {
                    note.Edit();
                });
        }

        const pinIcon = $('[data-note-id="'+this.id+'"] .note-icon-pin');

        if(this.data.isPinned) {
            pinIcon.hide();
        } else {
            pinIcon.show();
        }

        this.elTitleText.html(this.data.title);
        this.elBody.html(content);

        this.elLoader.hide();
        this.elTitle.show();
        this.elBody.show();

        UI.RefreshTimeout(function() {
            note.Handle_Rendered();
        });
    }

    Handle_Rendered()
    {
        this.rendering = false;

        this.UpdateLayout('Note has been rendered.');
    }

    /**
     * Called whenever the note is resized, to let masonry
     * adjust the layout accordingly. This includes resizing
     * the textarea in edit mode.
     *
     * @param {String|null} comments=null
     */
    UpdateLayout(comments)
    {
        if(!this.IsReady()) {
            return;
        }

        this.notepad.Masonry();
    }
}

window.Application_Notepad_Note = Application_Notepad_Note;
