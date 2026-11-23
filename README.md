# ntfy notifications for Moodle (message_ntfy)

Sends Moodle notifications (graded work, forum posts, due dates, anything Moodle notifies about) to the user's phone or computer through [ntfy](https://ntfy.sh), the free, open source push notification service. Use the public server at ntfy.sh or run your own.

## Set it up

1. Site administration > Messaging > Notification settings: turn on **ntfy**, then choose which notifications it may send.
2. Site administration > Plugins > Message outputs > ntfy: keep `https://ntfy.sh` or enter your own server. A server that requires sign-in to publish needs an access token (starts with `tk_`).
3. Each user installs the ntfy app (Android, iOS) or opens the ntfy web app, subscribes to a topic, and enters the same topic under Preferences > Notification preferences > ntfy.

## Good to know

- On ntfy.sh anyone who knows a topic can read it. The settings form suggests a random topic; users should keep it hard to guess. For private notifications, run your own server with access control.
- The notification title is the message subject, the text is the plain-text message (cut to ntfy's 4,096 byte limit), and tapping it opens the page in Moodle.
- A message only counts as sent when the server answers with a message id.
- A server on a private network address must be allowed in Site administration > General > Security > HTTP security.
- Deleted, suspended and no-login users are never sent anything.

Moodle 4.5 to 5.3. Built by DevLMS: https://devlms.com
