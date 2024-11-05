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
        this.refreshDelay = 30; // Seconds
        this.refreshTimer = null;
        this.initializing = true;
        this.addedNote = null;
        this.logger = new Logger('Notepad');
    }

    /**
     * Displays the notepad UI.
     * @public
     */
    Open()
    {
        this.logger.logUI('Opening the notepad...');

        application.disallowAutoRefresh('notepad');

        this.CreateContainer();

        this.container.show();

        this.LoadNotes();
    }

    Close()
    {
        this.logger.logUI('Closed the notepad.');

        application.allowAutoRefresh('notepad');

        this.container.hide();

        if(this.refreshTimer !== null) {
            clearTimeout(this.refreshTimer);
        }
    }

    LoadNotes()
    {
        const notepad = this;

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

        this.logger.logUI('Creating the notepad\'s container element.');

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

        var btnAdd = UI.Button(t('Add a note'))
            .SetTooltip(t('Adds a new, empty note to the notepad.'))
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
            '<h1 class="notepad-header">'+
                t('Your personal notepad')+
            '</h1>'
        );

        var body = $('<div/>');

        this.body = $(body)
            .addClass('notepad-body');

        this.body.append('<p class="abstract">'+t('Write down any information you like, which will follow you everywhere in %1$s.', application.getAppNameShort())+'</p>');

        var notes = $('<div/>');

        this.notesList = $(notes)
            .addClass('notepad-notes-list');

        this.body.append(this.notesList);
        this.body.append('<div style="clear:both"></div>');

        this.container.append(this.body);

        this.container.prependTo($('#content_area'));

        this.notesList.masonry({
            'itemSelector':'.notepad-note'
        });
    }

    /**
     * Appends the dom element of a note to the note
     * list container element.
     *
     * @param {jQuery} element
     */
    AppendNoteElement(element)
    {
        this.notesList.append(element);
        this.notesList.masonry('appended', element);
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

        const notepad = this;

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

        this.logger.log('Note ['+noteID+'] | Registering.');

        const note = new Application_Notepad_Note(this, noteID);

        this.notes.push(note);
    }

    /**
     * @param {Number} noteID
     * @returns {Boolean}
     */
    HasNoteID(noteID)
    {
        return this.GetNoteByID(noteID) !== null;
    }

    GetNoteByID(noteID)
    {
        let found = null;

        $.each(
            this.notes,
            /**
             * @param {Number} idx
             * @param {Application_Notepad_Note} note
             * @return {boolean}
             */
            function(idx, note)
            {
                if(note.GetID() === noteID) {
                    found = note;
                    return false;
                }
            }
        );

        return found;
    }

    Masonry()
    {
        if(this.initializing) {
           return;
        }

        this.logger.log('Applying masonry to note elements.');

        this.ApplyMasonry();
    }

    ApplyMasonry()
    {
        const list = this.notesList;

        // Using a short timeout to give the dynamically
        // created DOM elements the time to settle in their
        // dimensions before applying the masonry.
        UI.RefreshTimeout(
            function () {
                list.masonry();
            }
        );
    }

    Refresh()
    {
        this.logger.log('Refreshing all notes...');

        $.each(this.notes, function (idx, note) {
            note.Refresh();
        });
    }

    log(message, category)
    {
        application.log('Notepad', message, category);
    }

    CheckInitDone()
    {
        let ready = true;

        $.each(
            this.notes,
            /**
             * @param {Number} idx
             * @param {Application_Notepad_Note} note
             */
            function (idx, note)
            {
                if(!note.IsReady()) {
                    ready = false;
                    return false;
                }
            }
        );

        if(ready) {
            this.Handle_InitDone();
        } else {
            const notepad = this;
            UI.RefreshTimeout(function() {
                notepad.CheckInitDone();
            });
        }
    }

    // region: Events

    /**
     * Called when a new note has been added.
     * @param {Number} noteID
     */
    Handle_NoteAdded(noteID)
    {
        this.logger.logEvent(sprintf('Note [%s] | NoteAdded | Added successfully.', noteID));

        this.addedNote = noteID;

        this.LoadNotes();
    }

    /**
     * @param {Application_Notepad_Note} note
     */
    Handle_NoteRemoved(note)
    {
        var deleteID = note.GetID();
        var keep = [];

        $.each(this.notes, function (idx, checkNote) {
            if(checkNote.GetID() !== deleteID) {
                keep.push(checkNote);
            }
        });

        this.notes = keep;

        this.Masonry();
    }

    /**
     * Called when the notepad has started, and all
     * existing note IDs have been fetched.
     *
     * @param {Number[]} ids
     */
    Handle_IDsLoaded(ids)
    {
        this.logger.logEvent(sprintf('IDsLoaded | [%s] IDs have been loaded successfully.', ids.length));
        this.logger.logData(ids);

        const notepad = this;

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

        this.CheckInitDone();
    }

    Handle_InitDone()
    {
        this.logger.logEvent(sprintf('InitDone | All [%s] notes are rendered and ready.', this.notes.length));

        this.initializing = false;

        if(this.addedNote !== null) {
            this.logger.log(sprintf('Note [%s] | Opening after adding it.', this.addedNote));
            this.GetNoteByID(this.addedNote).Edit();
            this.addedNote = null;
        } else {
            this.Masonry();
        }
    }

    // endregion
}

window.Application_Notepad = Application_Notepad;
