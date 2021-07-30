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

        this.notepad = notepad;
        this.id = id;
        this.loaded = false;
        this.loading = false;
        this.deleting = false;
        this.elContainer = null;
        this.elTitle = null;
        this.elTitleText = null;
        this.elBody = null;
        this.elLoader = null;
        this.editMode = false;

        this.elTitleInput = null;
        this.elTextInput = null;

        this.CreateContainer();
        this.Refresh();
    }

    Refresh()
    {
        if(this.editMode || this.deleting)
        {
            return;
        }

        this.log('Refreshing the note...');

        if(!this.loading)
        {
            this.LoadData();
        }
        else
        {
            this.log('Ignoring, note is still loading data.');
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
        this.log('Loading the note data...');

        this.loading = true;

        var notepad = this;
        var payload = {
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
        this.log('The note date has been loaded.');
        console.log(data);

        this.data = data;

        this.Render();
    }

    Handle_DeleteSuccess()
    {
        this.Remove();
    }

    Remove()
    {
        this.elContainer.remove();
        this.notepad.Handle_NoteRemoved(this);
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

        this.editMode = true;

        this.RenderEditForm();

        this.elTextInput.focus();
    }

    ConfirmEdits()
    {
        this.elContainer.removeClass('note-editing');

        var title = this.elTitleInput.val();
        var content = this.elTextInput.val();

        this.ShowLoader();

        var payload = {
            'note_id': this.id,
            'title': title,
            'content': content
        };

        var note = this;

        application.createAJAX('NotepadSave')
            .SetPayload(payload)
            .Success(function (data) {
                note.Handle_SaveSuccess(data);
            })
            .Send();
    }

    Handle_SaveSuccess(data)
    {
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
        var note = this;

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
        var note = this;
        var payload ={
            'note_id': this.id
        };

        application.createAJAX('NotepadPin')
            .SetPayload(payload)
            .Error(t('Could not pin the note, the request failed.'))
            .Success(function() {
                note.Handle_PinSuccess();
            })
            .Send();
    }

    Handle_PinSuccess()
    {
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
        this.log('Creating the note container.');

        var note = this;

        this.elContainer = $('<div/>')
            .addClass('notepad-note')
            .attr('data-note-id', this.id);

        // Observe the note container element being
        // resized, to automatically update the layout.
        new ResizeSensor(this.elContainer, function() {
            note.UpdateLayout();
        });

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
    }

    Render()
    {
        this.log('Rendering the note\'s data.');

        var note = this;
        var content = this.data.html;

        if(content.length === 0)
        {
            content = $('<span class="notepad-note-placeholder">'+t('Click to write some notes...')+'</span>');
            content = $(content)
                .css('cursor', 'pointer')
                .click(function () {
                    note.Edit();
                });
        }

        var pinIcon = $('[data-note-id="'+this.id+'"] .note-icon-pin');

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

        this.UpdateLayout();
    }

    /**
     * Called whenever the note is resized, to let masonry
     * adjust the layout accordingly. This includes resizing
     * the textarea in edit mode.
     */
    UpdateLayout()
    {
        this.notepad.Masonry();
    }

    log(message, category)
    {
        application.log('Notepad note ['+this.id+']', message, category);
    }
}

window.Application_Notepad_Note = Application_Notepad_Note;
