# SLF - The missing doc !

Congratulations you made it to the doc !

In this doc I will try to be as thorough as possible.

## Basics

The framework is designed around the typical Model View Controller (MVC) architecture. Meaning you will have to get your hands dirty (with code ;-)) to get around it.

As stated in the intro (yes the README file!), there is nothing super fancy, no superduper ORM, no super high level templating engine, the goal here is to have minimum survivability tools (very basic templating, super basic database abstraction layer...) and to keep it fast and lightweight.

That being said, this won't let you go bare naked on the field. You'll have basic templating engine for the views. Models are classes that inherit from the 'Model' class that allows basic CRUD operations. Bonus point on data validation, I don't assume PDO is able to clean up every bit and byte, you should not either. Controller come with a minimalistic set of tools and injectors (mini but sweet !). And everything is built to handle multilang support (ok, not everything, DB model doesn't do it by itself !).

The entire thing allows more or less to add anything to it (as long as it's PSR-x compliant and you know composer and autoloader mecanisms!). But ... the framework itself is a little self dependent (my bad on this one, I rely on my own tools that was the point of this side project).

I have no ideas how it usually works so for this one the contoller is the center of the world, everything goes to it, everything else comes from it !

In short, I had a lot of fun coding it (this made me learn a lot about objects and all the framework thing in PHP and code in general). My main concern was to make it as simple to use as my poor mind can wrap itself around. On this I think I succeeded, maybe I could do better if I had more time, maybe not.

Anyway, it's MIT licensed, play with it as much as you want and **if you want to make me happy: build an awesome app with it** !

## Components

For some more details, follow the links ...

No priorities here, I'll mostly skip the basic stuff and explain what I added / did differently.

### Model

- Basic PDO wrapper (only tested with MySQL)
- Rule based data validation possibilities
- Very basic CRUD (Create Read Update Delete) query generator

### View

- Basic .php files (html + echoes)
- A few helpers to ... help you !
- Translation handling (via versions, more on that in the multilang topic)

### Controller

- Fills the void between views and models
- Handles a few neat things via auto-injectors
- Does not handle your mother-in-law !

### Routing

- Routes
- Controller discovery
- Namespaces handling

### Configuration

- Json based config
- default and custom are merged at runtime

### Multilang

- Allow virtually an unlimited number of languages
- Based on template files when you need to write a lot
- Based on json files when you want to be brief

### Cache

- Yes there is cache !
- No you don't have to pay for it !

### Sessions

- Session management
- Notifications
- Admin

### Helpers

- HTML (could be improved !)
- JSON (to make it a little more friendly)
- Form (because TWBS forms are a pain !)

### Design

- Twitter Bootstrap (v3.0.x SASS)
- HTML 5 / CSS 3
- Javascript (jquery)

### Included

- User management (+ user admin)
- Cache management (pages are only admin)
- Custom logging (disabled by default)


## Architecture

This framework is design to use modern technology, to run it you'll need PHP >= 5.5. To make it smooth I strongly recommend FPM and an opcode caching extension (OPCache is probably your best bet at the moment).

PHP / MySQL is THE couple of choice (make it MySQL 5.5 or newer) and of course use the mysqlnd as in Native Driver extension.

### Directory structure

This repository (you do have the whole thing right!?!) contains a bunch of directories and files at it's root.

Here are the main and most important ones

- design/ (where scss files live)
- doc/ (I guess you are reading it now!)
- sql/ (everything database related, like create scripts)
- **www/ (root of the framework itself)**
- config.rb (sass/compass config file, to compile scss to css)
- gulpfile.js (basic gulp task to refresh your browser as you code and recompile scss when needed)
- LICENSE.txt (very important file that should be read and signed with your blood!)
- package.json (installs required node.js modules, for gulp mainly)
- README.md (read it to understand)

I am not a fan of .htaccess files so everything should be in a virtualhost config (for apache, for nginx or whatever other web server find the related doc).

### Installation

- composer
- docroot
- done !