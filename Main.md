In due course, this Wiki will contain documentation, how-tos and so forth. For now, here's a list of the current state of the project as well as features that are planned.

## Roadmap ##

The following is a list of planned or desirable features, broken up into stages. The stages will likely be tackled in order, while the features within each stage may be dealt with in any order.

### Stage 1: Basic functionality ###

  * ~~Create a form definition~~
  * ~~Render a form definition to HTML~~
  * ~~Submit a form (using standard POST/GET)~~
  * ~~Validate a form~~
  * ~~Show errors~~
  * Supported fields:
    * ~~Hidden~~
    * ~~Text~~
    * ~~Password~~
    * ~~Textarea~~
    * ~~Checkbox~~
    * ~~Radio button(s)~~
    * ~~Select~~
    * ~~Select Multiple~~
    * ~~File~~
    * ~~Button~~
    * ~~Date (basic)~~
  * Validators
    * ~~Required~~
    * ~~Numeric (with minimum and maximum values)~~
    * ~~Length (with minimum and maximum values)~~
    * ~~Email~~
    * ~~URL~~
    * ~~Confirm (force user to enter value twice)~~
    * ~~Regular Expression~~
    * ~~Options (for select, select multiple and radio buttons)~~
    * ~~Reject values (define a list of arbitrary values that are not valid)~~
    * ~~Date Range~~
    * Files
      * ~~Type~~ (very basic checking, needs to be improved)
      * ~~Size~~
      * ~~Number of files~~
  * Filters
    * ~~HTML~~

### Stage 2: Initial JavaScript support ###

  * Validate a form before submission, using JavaScript
    * ~~Required~~
    * ~~Numeric (with minimum and maximum values)~~
    * ~~Length (with minimum and maximum values)~~
    * ~~Email~~
    * ~~URL~~
    * ~~Confirm (force user to enter value twice)~~
    * ~~Regular Expression~~
    * ~~Reject values (define a list of arbitrary values that are not valid)~~
    * Unsupported validators (not possible or not relevant in JavaScript)
      * Options
      * Files
        * Type
        * Size
        * Number of files
  * ~~Show errors~~
  * Fields
    * ~~Date (advanced)~~
    * ~~Rich text editor (tinyMCE)~~
  * Filters
    * XSS (using http://htmlpurifier.org)

### Stage 3: Full JavaScript support ###

  * Events -- allow third-party code to hook into form events
    * ~~onBeforeSubmit~~
    * onBeforeValidate
    * onAfterValidate (return validation status)
    * onChange (for a field, for entire form)
  * ~~Load and render form via Ajax~~
  * ~~Submit form via Ajax~~
  * Fields
    * File (advanced)

### Stage 4: Form Builder ###

  * Visual utility for creating form definitions
    * Create/edit/delete elements, move them within the form
    * Manage validation rules
    * Preview
    * Export

### Desirable features: ###
  * Fields
    * ~~Captcha (basic)~~
    * ReCaptcha
  * Autofocus (focus on a field on page load)
  * Tab order
  * Auto-email form submission
  * Auto-change detect and validation (so that fields show as valid/invalid immediately)