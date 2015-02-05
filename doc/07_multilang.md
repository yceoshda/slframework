# Multilang

## How it works

SL Framework was built from the start to handle multilang natively. This is achieved by using 2 layers of translations, templates can be localized, then all small texts should be stored in localized JSON files.

When loading a template, the framework templating engine will automatically search for a localized version of the template file, then if none are found it will load a non-localized version. This behavior can be usefull when a template does not need localized versions.

Translation files are JSON files. If you plan on using them to translate forms (and you should!), form fields should be placed in a 'fields' section of the file (please refer to an existing translation file in www/vendor/spacelife/translation for examples). Buttons should be placed in a 'btn' section. You can use the same translation file to hold several pages by leveraging native json format.

When use one or the other method?

Don't decide, mix to suit your needs! You'll probably want to put large texts in templates and small messages in json files.

## Caching (JSON)

As for the configuration, the reference files are not supposed to be localized, but they should hold the structure of the localized versions. Structural and localized versions are merged at runtime (localized items overwrite structural ones). The merge result is then cached to save on computing time for the next time.

For more information on cache, please read the Cache doc topic.

## Adding new languages

SL Framework comes with english and french translations. To add a new language, just create whatever translation json files and templates needed (coding the language with their official 2 letters abv.), and add those 2 letters to the list of available languages ('langs') in the configuration. You need to add them to the relevent views of course.
