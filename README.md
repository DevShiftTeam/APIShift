<div align="center">
<img width="100px" src="https://raw.githubusercontent.com/DevShiftTeam/APIShift/master/images/apishift-logo.png">

# APIShift Engine
</div>

APIShift is a platform with a set of helpful tools for building your own API/server with a configurable architecture that lets you implement whatever type of style you want, whether it'll be REST, SOAP or your own monster you wish to implement. The environment which APIShift sets you, gives you flexibility and control over the following concept:
 * **Requests**: Develop your own requests & their responses with a helpful and configurable set of tools, and a ready to use workflow set up for you. (*Status*: **``Ready``**)
 * **Session**: Manage the sessions, their different states, roles, life-cycle and data during run-time with a simple interface. (*Status*: **``Development``**)
 * **Database**: Manage data & structures in different databases using an interface that lets you automate and manage multiple databases and types of databases. (*Status*: **``Development`` ``Planning``**)
 * **Algorithms**: Creating logical procedures using a graph-based UI or your own code to define procedures and attach them to different areas of the code/other procedures/lifecycle of your application using our interface. (*Status*: **``Development`` ``Planning``**)
 * **Authorize**: Buid your unique authorization processes and attach them to any part of your system easily. (*Status*: **``Development`` ``Planning``**)
 * **Analyze**: Track anything you want in your system and collect data about it, then create your own analyzation tools or use our pre-made tools to present data and conclusions about the system and how it functions. (*Status*: **``Planning``**)

If you want, you can use your own tools, dependencies and modifications, the architecture lets you add on top of it whatever you want to integrate and use.

## Table of contents
- [APIShift Engine](#apishift-engine)
  - [Table of contents](#table-of-contents)
  - [About](#about)
    - [Meet Our Team](#meet-our-team)
  - [Installation](#installation)
    - [Requirements](#requirements)
  - [Understanding the system](#understanding-the-system)
  - [Usage](#usage)
    - [Requests (API)](#requests-api)
    - [Hardcoded (Back-End)](#hardcoded-back-end)
- [About Versions](#about-versions)
  - [Current Versions](#current-versions)
  - [Expected in next versions](#expected-in-next-versions)
- [Projects that made this possible](#projects-that-made-this-possible)

## About
APIShift is an open-source PHP system that was designed by Sapir Shemer and its releases are maintained by [DevShift](https://devshift.biz). If you want to help and join the community, you are more than welcome to contribute. Just visit the [DevShift](https://devshift.biz) site or contact Sapir in any way you feel comfortable with: [LinkedIn](https://www.linkedin.com/in/sapir-shemer/), [Facebook](https://www.facebook.com/sapir.shemer).
Released under the MIT license.

### Meet Our Team
 * [Sapir Shemer](https://github.com/LessComplexity) as the architect.
 * [Eran Bodokh](https://github.com/Bodokh) as the Front-End lead.

## Installation
Clone the repository, upload it to your server, and then just visit the server through the web - the system will automatically redirect you to the installation page and will install the configurations and database by the data that you provide during installation.

Notice that the [configurations file](machine/core/Configurations.php) should be writable (in linux: `sudo chmod 666 machine/core/Configurations.php` should solve this), otherwise installation will return a permission denied error. If the DB schema provided doesn't exist, then the installer will attempt to create the schema, if so, then make sure the DB user provided has the right access to do so, otherwise you can create the schema yourself.

### Requirements
 * Web server
 * PHP7+
 * APCu or Redis or Memcached enabled on your PHP engine
 * MySQL

## Understanding the system
One of our goals is to provide users access to a full ecosystem of information about the system. Everything from tutorials, documentation and the code design and flow of the system. The purpose of releasing these documents is to help people understand the system fully. 

The main folders of the system indicate the basic idea of where development is done:
* [**Root Folder**](/): Contains the system and displays content to the end user.
* [**control/**](control/): Stores the UI code & style of the control panel of the devshift system.
* [**machine/**](machine/): Stores the Back-End code for the APIShift system.
* [**UI/**](UI/): Stores the UI code the the main page - can actually have as many diffrent folders with different names to build your app, the UI is provided by default and not confined to this name or style, so do whatever you want.
* [**externals/**](externals/): External dependencies and libraries the the APIShift system uses.
* [**images/**](images/): Stores images that the APIShift system uses.
* [**docs/**](docs/): Stores HTML pages the provide information and documentation about the system.

And these are the documents that serve as the information infrastructure we set up for users:
 1. __[Architecture and design](ARCHITECTURE_AND_DESIGN.md)__: Provide a map showing the system from the view point of the code, and the developer - Reading this document is the best way to get insight into the system (besides running it and reviewing the code on your own, it's better to start here).
 2. __Documentation__: Install the system and navigate to `http://your-server/doc/server` for server-side documentation. Client-side documentation comming soon.
 3. __Tutorial__: Comming soon

## Usage
After Using APIShift to build an API or Back-End logic and controllers, you access the controllers/other parts and features of the system in 2 different ways presented below.

### Requests (API)
Any request is actually a call to a method of a certain controller. You specify the controller and method in GET form (specifying `c` for controller and `m` for method, yes we keep it minimal), and the data you want to pass to the method in any form you like (Whether GET or POST - you define how the function recieves it). Controller methods belonging to the system will always get data into the function in POST form. Usually to keep a standard we recommend you to pass any data to a controller function in POST form even in your own methods. The url of the request should look similar to this:

```
https://example-site.com/machine/API.php?c=<ControllerName>&m=<MethodName>
```

Example in JS using the [APIShift.js](control/UI/scripts/APIShift.js) library:
```javascript
// 
APShift.API.request("<Controller Name>", "<Method Name>", { <Request Body> },
    function(response) {
        /**
         * response in an object containing a status and the response data as either a string or an object
         */
        // Success message
        if(response.status == APIShift.API.status_codes.SUCCESS) APShift.notify(status.data, "success");
        // Error message
        else if(response.status == APIShift.API.status_codes.ERROR) APIShift.notify(status.data, "error");
        // Any other status
        else APIShift.notify("Unknown error!", "error");
    }
);
```

### Hardcoded (Back-End)
> **Notice**: Currently this title is out of date and will be updated as soon as development on this usage will complete

APIShift also provides the [APIShift.php](machine/APIShift.php) file which loads the system whenever you include it. This way you can make PHP pages that include the APIShift system and construct the view on server-side instead of calling an API to recieve data and then construct the view (using JS for example). Then any call for a controller done by using the `Authorizer::aurthorizeAndRun` method to ensure that an authorization process is done for the method call - or you can just place your own authorization processes and triggers.

```php
<?php
include "machine/APIShift.php";
use APIShift\Core\Authorizer as Authorizer;
?>
<h1><? echo Authorizer::authorizeAndRun(APIShift\Controllers\Page::class, "getPageTitle", ["name" => "Home"]); ?></h1>
```

# About Versions
## Current Versions
Data on the current versions will be given here

## Expected in next versions
Data on the next versions will be present here

# Projects that made this possible
 * [VueJS](https://github.com/vuejs)
 * [Vuetify](https://github.com/vuetifyjs)
 * [http-vue-loader](https://github.com/FranckFreiburger/http-vue-loader)
 * [Vuebar](https://github.com/DominikSerafin/vuebar)