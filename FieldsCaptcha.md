# Fields:: CAPTCHA #

A so-called CAPTCHA ("Completely Automated Public Turing test to tell Computers and Humans Apart") field is, as the name suggests, a method for preventing forms from being filled in and submitted automatically by a computer, rather than by a human being.

It does this by showing a small image which contains (usually) a short code which the user must copy into a text field. Because it's hard for a computer to recognise text in an image, this makes it difficult to write a script that can successfully enter the required code.

In creating the CAPTCHA field type I've deliberately made certain choices that some people might question. Below is an explanation of those choices.

## Why isn't the text obscured in any way? ##

Briefly: _any_ kind of CAPTCHA is likely to be 'good enough' for the vast majority of applications.

Unless your site is offering something particularly desirable or financially valuable, chances are that it's only ever going to be targeted by fully automated scripts, which are unlikely to have the capability to identify and solve any kind of CAPTCHA field. In these cases, making the text harder to read is only going to make life harder for genuine visitors who want to use your form.

Should your site be one of the few that is targeted by more sophisticated attackers, then the problem is that the sort of CAPTCHA that really makes life difficult for them -- by being really, really hard to read -- often becomes an insurmountable obstacle for regular people.