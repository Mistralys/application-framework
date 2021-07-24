'use strict';

class Application_Notepad
{
    /**
     * @constructor
     */
    constructor()
    {
        this.ERROR_ADD_NOTE_REQUEST_FAILED = 90201;

        this.container = null;
        this.body = null;
        this.notesList = null;
        this.notes = [];
        this.refreshDelay = 5; // Seconds
        this.refreshTimer = null;
    }

    /**
     * Displays the notepad UI.
     * @public
     */
    Open()
    {
        this.log('Opening the notepad...');

        this.CreateContainer();

        this.container.show();

        this.LoadNotes();
    }

    Close()
    {
        this.log('Closed the notepad.');

        this.container.hide();

        if(this.refreshTimer !== null) {
            clearTimeout(this.refreshTimer);
        }
    }

    LoadNotes()
    {
        var notepad = this;

        application.createAJAX('NotepadGetIDs')
            .Success(function(data) {
                notepad.Handle_IDsLoaded(data);
            })
            .Send();
    }

    /**
     * @private
     */
    CreateContainer()
    {
        if(this.container !== null)
        {
            return;
        }

        this.log('Creating the notepad\'s container element.');

        var notepad = this;
        var div = $('<div></div>');

        this.container = $(div)
            .addClass('app-notepad')
            .css('display', 'none');

        var btnClose = UI.Button('')
            .SetIcon(UI.Icon().Close())
            .SetTooltip(t('Closes the notepad.'))
            .AddClass('pull-right')
            .Click(function() {
                notepad.Close();
            })

        var btnAdd = UI.Button(t('Add note'))
            .SetIcon(UI.Icon().Add())
            .AddClass('pull-right')
            .SetStyle('margin-right', '10px')
            .MakePrimary()
            .Click(function() {
                notepad.AddNote();
            });

        this.container.append(
            btnClose.Render()+
            btnAdd.Render()+
            '<h1 class="notepad-header">'+t('Your personal notepad')+'</h1>'
        );

        var body = $('<div/>');

        this.body = $(body)
            .addClass('notepad-body');

        this.body.append('<p class="abstract">'+t('Write down any information you like, which will follow you everywhere in %1$s.', application.getAppNameShort())+'</p>');

        var notes = $('<div/>');

        this.notesList = $(notes)
            .addClass('notepad-notes-list');

        this.body.append(this.notesList);

        this.container.append(this.body);

        this.container.append('<div style="clear:both"></div>');

        this.container.prependTo($('#content_area'));
    }

    /**
     * Appends the dom element of a note to the notes
     * list container element.
     *
     * @param {jQuery} element
     */
    AppendNoteElement(element)
    {
        this.notesList.append(element);
    }

    /**
     * Adds a new note: sends a request to the `NotepadAdd`
     * method, which creates an empty note. This returns the
     * added note ID.
     *
     * @see Handle_NoteAdded
     */
    AddNote()
    {
        this.log('Adding a new note...');

        var notepad = this;

        application.createAJAX('NotepadAdd')
            .Success(function(data) {
                notepad.Handle_NoteAdded(data.note_id);
            })
            .Error(t('Could not add a new note.'), this.ERROR_ADD_NOTE_REQUEST_FAILED)
            .Send();
    }

    /**
     * Registers a note by creating a note instance,
     * and adding it to the internal collection. The
     * notes then render themselves autonomously.
     *
     * @param {Number} noteID
     */
    RegisterNote(noteID)
    {
        if(this.HasNoteID(noteID)) {
            return;
        }

        this.log('Registering new note ['+noteID+'].');

        var note = new Application_Notepad_Note(this, noteID);

        this.notes.push(note);
    }

    /**
     * @param {Number} noteID
     * @returns {Boolean}
     */
    HasNoteID(noteID)
    {
        var found = false;

        $.each(this.notes, function(idx, note)
        {
            if(note.GetID() === noteID) {
                found = true;
                return false;
            }
        });

        return found;
    }

    Refresh()
    {
        this.log('Refreshing all notes...');

        $.each(this.notes, function (idx, note) {
            note.Refresh();
        });

        var notepad = this;

        this.refreshTimer = setTimeout(
            function () {
                notepad.LoadNotes();
            },
            this.refreshDelay * 1000
        );
    }

    log(message, category)
    {
        application.log('Notepad', message, category);
    }

    // region: Events

    /**
     * Called when a new note has been added.
     * @param {Number} noteID
     */
    Handle_NoteAdded(noteID)
    {
        this.log('New note was added successfully with ID ['+noteID+'].');

        this.RegisterNote(noteID);
    }

    /**
     * @param {Application_Notepad_Note} note
     */
    Handle_NoteDeleted(note)
    {
        var deleteID = note.GetID();
        var keep = [];

        $.each(this.notes, function (idx, checkNote) {
            if(checkNote.GetID() !== deleteID) {
                keep.push(checkNote);
            }
        });

        this.notes = keep;
    }

    /**
     * Called when the notepad has started, and all
     * existing note IDs have been fetched.
     *
     * @param {Number[]} ids
     */
    Handle_IDsLoaded(ids)
    {
        this.log('Note IDs have been loaded successfully: ['+ids.join(', ')+'].');

        var notepad = this;

        $.each(ids, function (idx, noteID) {
            notepad.RegisterNote(noteID);
        });

        // Clean up obsolete notes, removed serverside
        // for some reason (notepad editing in another tab,
        // for example).
        $.each(this.notes, function (idx, note)
        {
            if(!in_array(note.GetID(), ids))
            {
                note.Delete();
            }
        });

        this.Refresh();
    }

    // endregion
}

window.Application_Notepad = Application_Notepad;
