A comprehensive library for creating and validating forms in PHP. Aims to do as much of the donkey work as possible while still allowing flexibility.

Features:
  * Simple but powerful form building process
  * Support for all standard form elements
    * text
    * textarea
    * radio buttons
    * select, select-multiple, with optional 'optgroup' separators
    * checkbox, multiple checkboxes
    * password
    * hidden
    * file
    * date
    * rich text
    * CAPTCHA
  * Simple but powerful options for validation -- performed both client-side (via JavaScript) and server-side
    * Required field
    * Length (maximum and minimum)
    * Numeric, with optional minimum/maximum value
    * Email
    * URL
    * Regular expression
    * Confirmation -- automatically show a duplicate field whose value must match (useful for passwords, email addresses)
    * File type, size and number
    * Reject values (specify certain values that will be automatically rejected)
  * Support for internationalisation (i18n)
  * Fully XHTML Strict 1.0 compliant output

Please note that this project is currently in **alpha** state, meaning:
  * It is not feature-complete
  * Features that are present may change or be removed
  * It is possible -- likely -- that there are severe bugs

As such, it is not recommended for production use at this time.

You can see a summary of the current status of the project [here](http://code.google.com/p/in4ml/wiki/Main).

### Licence ###

Google Code only lets you select one license, but if you look at the code it is in fact dual-licensed with the MIT and GPLv2 licences. Simply put, you may choose whichever license suits you best and ignore the other.