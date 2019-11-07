# Facebook Feed Plugin by Bjoern Bohr


## Installation
Installing the Facebook-feed plugin can be done in one of two ways. Using GPM (Grav Package Manager) installation method or the manual method via a zip file.

## GPM Installation 

The simplest way to install this plugin is via the Grav Package Manager (GPM) through your system's Terminal . From the root of your Grav install type:

**bin/gpm install facebook-feed**

## Manual Installation

1. Download the Plugin
2. Place the facebook-feed folder inside your Projects Plugin Section
> The path should look like this: **/your/site/grav/user/plugins/facebook-feed**

## Usage

1. go to [developers.facebook.com](https.developers.facebook.com) and create a new App and generate an Access Token
2. Log into the Backend and open the Facebook Feed Plug in
3. Past the Facebook Page Id and your previously created AccessToken into the provides fields
3. add the **{{ facebook_feed() }}** function anywhere to your site
