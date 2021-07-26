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

    CreateContainer()
    {
        this.log('Creating the note container.');

        var note = this;

        var container = $('<div/>');

        this.elContainer = $(container)
            .addClass('notepad-note');

        var title = $('<div/>');

        this.elTitle = $(title)
            .addClass('notepad-note-title')
            .hide();

        this.elTitle.append(
            UI.Icon().DeleteSign()
                .AddClass('pull-right')
                .AddClass('notepad-note-icon')
                .AddClass('note-icon-delete')
                .MakeDangerous()
                .SetTitle(t('Delete this note.'))
                .Click(function () {
                    note.DialogDelete();
                })
                .Render()
        );

        this.elTitle.append(
            UI.Icon().Edit()
                .AddClass('pull-right')
                .AddClass('notepad-note-icon')
                .AddClass('note-icon-edit')
                .MakeInformation()
                .SetTitle(t('Edit this note.'))
                .Click(function () {
                    note.Edit();
                })
                .Render()
        );

        var titleText = $('<span/>');
        this.elTitleText = $(titleText)
            .addClass('notepad-note-title-text');

        this.elTitle.append(this.elTitleText);

        var body = $('<div/>');

        this.elBody = $(body)
            .addClass('notepad-note-body')
            .hide();

        var loader = $('<div/>');

        this.elLoader = $(loader)
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
        if(content.length === 0) {
            content = $('<span class="notepad-note-placeholder">'+t('Click to write some notes...')+'</span>');
            content = $(content)
                .css('cursor', 'pointer')
                .click(function () {
                    note.Edit();
                });
        }

        this.elTitleText.html(this.data.title);
        this.elBody.html(content);

        this.elLoader.hide();
        this.elTitle.show();
        this.elBody.show();
    }

    log(message, category)
    {
        application.log('Notepad note ['+this.id+']', message, category);
    }
}

window.Application_Notepad_Note = Application_Notepad_Note;
