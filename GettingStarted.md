# Getting started with in4ml #

in4ml is designed to do as much as possible with a little effort as possible. Of course, it's not magic so you will need to spend some time getting it set up. However this process is fairly simple and shouldn't take more than ten minutes or so.

## Installation ##

The first step is, naturally, to download the code. Once you've done that, you need to copy the code to your web server.

### Code structure and file locations ###

The code for in4ml is split up into three directories:
  * in4ml: this contains all the core PHP code for the library. You should never need to edit any of the files in this directory.
  * in4ml\_resources: this contains all client-facing files such as CSS and JavaScript. Again, you should never need to edit any of these files.
  * in4ml\_local: this directory contains all code that is specific to your installation of in4ml, such as the configuration file. It is expected that you will need to edit some or all of the files in this directory.

Of the three directories, only in4ml\_resources needs to be visible to the web (i.e. accessible via a browser). It's entirely your choice, but if you would like to do so you may move the other directories out of your web root so that they are not accessible to the web. The path to these directories can be set during configuration.

When you've installed the code, go to the in4ml\_local directory and rename the file

_im4ml.config.php.sample_

to

_im4ml.config.php_

This file holds various configuration settings (see below).

## Configuration ##

The im4ml.config.php file, in your in4ml\_local directory, is where various cpnfiguration directives are set. There are a number of configuration directives available but only two that are required:

  * **`$config[ 'form_prefix' ]`** sets the prefix for all form class names. Setting this allows you to give all form classes a prefix unique to your application.
  * **`$config[ 'path_resources' ]`** is the web path to the in4ml resources directory. So if it's in the root of your web site, it would be '/in4ml\_resources/'.

## Creating your first form ##

Creating a form starts by creating a new file in your forms directory (in your in4ml\_local directory). Assuming the form\_prefix config setting is '`MyForm`', let's call it '`MyFormTest`'.

```
class MyFormTest extends in4mlFormPHP{
}
```

## Calling the form ##

## Doing something with the results ##