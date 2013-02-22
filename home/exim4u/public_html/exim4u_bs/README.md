## Exim4UI
a [bootstrap](http://twitter.github.com/bootstrap/)pified version of the [Exim4U](http://exim4u.org/) Email Admin UI.

### Changes
#### Files removed:
* old stylesheet (/style.css)

#### Files added:
* new stylesheet: [Twitter Bootstrap 2.2.2](http://twitter.github.com/bootstrap/). */css/bootstrap.css* 
* new javascript file: [Twitter Bootstrap 2.2.2](http://twitter.github.com/bootstrap/). */js/bootstrap.min.js*
* new [jQuery](http://jquery.com/) (1.9.0) file. */js/jquery.min.js*
* new javascript file for table styling. */js/scripts.js*   

#### Content changes:
* stripped unused inline-styles and classes  
* tidied and fixed HTML markup

---

### Usage
* backup your *public_html/exim4u/* directory  
* copy the files from this repo into your Exim4U installation and overwrite existing files

---

### About  
* the HTML markup is now free from inline classes
* *bootstrap.css* and *bootstrap.js* are unmodified stock files from Bootstrap 2.2.2
* CSS classes used are:   
`.btn` for submit buttons  
`.input-append`, `.add-on` to append text to input fields.  
`.control-label`, `.controls`, `.form-actions` for login fields.  
`<table>` classes are injected through */js/scripts.js*
* styling intentionally kept to a minimum. Feel free to **add your own styles** and classes to the */css/bootstrap.css* stylesheet
* the functionality and PHP code remains untouched  
It's just a make-up release to provide a clean slate :)

---

### Screens
[[Link1](https://www.evernote.com/shard/s1/sh/a577c5e8-1767-4e22-821e-5169ecc755d3/5d9855242812ac208030d567c9c8baeb/res/fa084236-4202-4025-b7d6-b78bedab818b/1-20130207-214911.png.png?resizeSmall&width=832)]
[[Link2](https://www.evernote.com/shard/s1/sh/5aa61368-c39a-4389-b68a-1ec98b7eef3a/bc673edd0646de424be14999b68ca4f7/res/d9c7ab06-24be-49c4-9aa9-51e66b0c9d2d/2-20130207-214918.png.png?resizeSmall&width=832)]
[[Link3](https://www.evernote.com/shard/s1/sh/70143ad4-b454-4882-a123-5315e5f7e1b8/8a8b0788d5dbce995d1b7c4f992aed32/res/c26d5a2f-28b2-477b-9e45-e335cdc46b5a/3-20130207-214927.png.png?resizeSmall&width=832)]

---
### Todo
* add some kind of basic templating
* branch a heavier styled version for out-of-the box usage
