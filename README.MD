# W2G2
Nextcloud plugin that allows the users to lock files and folders.

It works with files and folders owned by the user, shared by another user or inside a group folder. 

## How it works

This app works only for the web version of Nextcloud. 

When a user 'locks' a file the app will add a pseudo-lock to the file actions (read, modify, share, etc.).

The pseudo-lock works by removing the click listeners and hiding the buttons in JavaScript.

The file can only be 'unlocked' by the original user that locked the file or an admin (via the settings panel where all files locks are listed).


## Original Author

Patrick Hoffmann - https://github.com/Patt92

## Migrate from Working2gether to W2G2

After installing W2G2, enter this command in DB:

  `insert into oc_locks_w2g2 select * from oc_locks_w2g;`

**Note**: This doesn't work from version 1.1.0 forward as we changed DB table to keep the file ID instead of the path. A workaround would be to install a version prior to 1.1.0, migrate the data using the above command and then upgrade the app, which would update the DB table.
