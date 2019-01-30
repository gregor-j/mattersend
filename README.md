# Mattersend

[![License: MIT][license-mit]](LICENSE)

Send messages from CLI to Mattermost via webhooks.

## Usage

Either download the `mattersend.phar` from the [releases] page, or use `composer require gregorj/mattersend` to add it to your project.

Create a [webhook] in Mattermost.

Define the environment variable `MATTERSEND_WEBHOOK` and set the value to the webhook you just created.

Send your first message:

```sh
mattersend send "Hello World"
``` 

See `mattersend send --help` for more options.

## Custom sender avatars

Create a few avatar images and put them in a single directory (e.g. `~/images/StarWars/`). The file names will be the avatar names, so you might want to rename them. In case the avatar should have a sender name in Mattermost append this sender name in `(``)`.

Examples:

* `happy anakin (Anakin Skywalker).png` The avatar name will be `happy anakin` and the default sender name will be `Anakin Skywalker`.
* `evil anakin (Anakin Skywalker).png` The avatar name will be `evil anakin` and the default sender name will be `Anakin Skywalker`.
* `Millenium Falcon.png` The avatar name and the default sender name will be `Millenium Falcon`.

Upload these files to a webserver and note the URL prefix to reach these files (e.g. `https://images.example.com/starwars/`).

Create an avatar JSON file- in this example `~/images/starwars.json`:
 
```sh
mattersend images -p https://images.example.com/starwars/ ~/images/StarWars/ ~/images/starwars.json
```

Define the environment variable `MATTERSEND_AVATARS` and set the value to `~/images/starwars.json`. You can upload the JSON to the webserver too and use the URL to the JSON as value too.

Search for the avatars you just created. `mattersend search anakin` would return 

```
Anakin Skywalker: happy anakin
Anakin Skywalker: evil anakin
``` 

You can use the avatars in your message now:

```sh
mattersend send -a "happy anakin" "Yes, I did it!"
```


[license-mit]: https://img.shields.io/badge/license-MIT-blue.svg
[releases]: https://github.com/gregor-j/mattersend/releases
[webhook]: https://docs.mattermost.com/help/settings/integration-settings.html
