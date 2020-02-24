<div align="center">
<img width="100px" src="https://gitlab.com/lesscomplexity/devshift-engine/-/raw/master/images/DevLogo.png">

# APIShift Code Design
</div>

This document organizes the different components, sub-components and their properties that make up the engine. The goal of this document is to provide both a high level overview of the architecture of the system and a guide for the specifics of functionality and algorithmics behind the engine. All new systems and components first need to be integrated in the code design before going into development stages to keep a clean, understandable flow of development, organization and efficiency. It is always better to first design the components and overall system before developing, it helps everyone to follow up with the same ideas and standards when developing and contributing to the system.

## Architecture Overview
<div align="center">
<img width="50%" src="https://gitlab.com/lesscomplexity/devshift-engine/-/raw/master/images/Architecture.png">
</div>

### Base Concepts
The system defines 3 main base concepts that are said to make a base for a whole API/Back-End, and provides modular and scalable objects as follows:
1. __Core__: The basic data for connection to the main database, the username and password for the APIShift system, and other hardcoded metadata. The core file is defined fully during system installation.
    * [Core Configurations Class](machine/core/Configurations.php)
2. __Database__: The systems that manage and communicate with the database to access the long-term memory from code, translates between different database models.
    * [Data Structure Manager & Translator Class](machine/core/DataModelManager.php)
    * [Item Class](machine/core/Item.php)
    * [Relation Class](machine/core/Relation.php)
    * [PDO Objects Collection Class](machine/core/DataModelManager.php)
3. __Session__: The systems that manage the session and session data changes. Each session has a structure of a PHP array (key-value) defining the structure of the session, and a structure with data at a given time is referred to as a session state.
    * [Session State Management Class](machine/core/SessionState.php)

### Mid-Level Concepts
The system also defines 3 more concepts that use the base concept to build up the logic, restrictions and analysis of the API, and as follows, define modular and scalable objects to handle it:
1. __Logic__: All the files and classes that make use of the session, database and core of the API to make the requests complete. All the logical elements are stored in the [models folder](machine/models), and also made viable by creating procedures using the system's UI and attaching them using triggers controllers, models or the lifecycle of the API.
    * [Procedure Management & Placement Class](machine/core/Task.php), Each task is made from different processes.
    * [Processes Management Class](machine/core/Process.php), Each process comes to life by combining operations.
    * [Operations Management Class](machine/core/Operation.php), Operations are made using pre-defined Rules or existing functions
    * [Rules Management Class](machine/core/Rule.php)
2. __Access__: Systems that are attaches to requests and controllers to validate the acces of a user to the request or the data that a client wishes to access. The Access management tools are based on Tasks & Processes and complete autorization processes using the session state which is made to store data that should indicate who is the client.
    * [Authorization Class](machine/core/Authorizer.php)
3. __Analysis__: Systems that are attached to different components of the system to accumulate usage data on the different components and the data that is transferred in them.

Around all of these definitions, the engine defines __Controllers__ as the combining part of the API. The controllers are objects that contain all the possible requests that can be made to an API. Each controller makes use of logic, and is surrounded by access and analysis methods to complete the full features of the API.

## Project Structure
Here we will review the filesystem structure of the APIShift framework before getting into specifics.

### Back-End
The back-end of the system is in the [machine folder](machine/) where you can find 4 different folders:
 * __[Core folder](machine/core)__: The folder containing all the core files of the system. The core files are classes that make up the main workflow of the system and contain methods to work with the system's main features. Any new core system will be integrated into the existing file or as a new file. Everything in the core folder should be under the namespace `APIShift\Core`.
 * __[Models folder](machine/models)__: Contains any classes that are used to make logical and database specific operations outside the scope of the system - made for users of the system. If you are contributing then you will probably touch every folder besides this one, unless you are adding features for managing extensions or helper functions, as the extensions management class and helper class exist in this folder. Everything in the models folder should be under the namespace `APIShift\Models`.
 * __[Controllers folder](machine/controllers)__: The controllers holding the methods that define the requests that can be made to system as the [readme](README.md) explains in the usage section. Each controller method validates the request and then walks through the necessary models and core files to complete the request. The authorizer handles the authorization and access rules for user-made methods - but if you are making methods for the control panel and developers of the system make sure to use the `Authorizer::authorizeState()` function which exists if the user is not in admin state - which is set only of the developer logs in to the framework. Everything in the controlelrs folder should be under the namespace `APIShift\Controllers`.
 * __[Data folder](machine/data)__: Data files outside of the scope of PHP scope - ini and sql files that load and install the system and other helpful files for configurations and other stuff.

And there are 2 main files in the back-end:
 * **[APIShift](machine/APIShift.php)**: A simple file that loads the autoloader of the system - which knows how to interpret the framework's namespaces with ease, loads the session and starts the main connection with the database.
 * **[API](machine/API.php)**: This files integrated the API workflows for each request, it first calls the APIShift file to load the system, then it validates the request, calls the authorizer to authorize the request, and runs the desired controller & method - the only way to make API requests to any controller should be only from this file, as it integrates the authorization process. If you build your views from PHP files than you need to use only the APIShift file as explained in the [readme](README.md)'s usage section.

### Control panel UI
The second most important structure to understand for contributers is the UI of the control panel which sits in the [control folder](control/). The control panel has a [UI folder](control/UI/) that contains all the UI components and an [index file](control/index.html) that integrates them together to make the single-page application of the control panel. The UI folder contains 4 sub-folder:

 * __[Components](control/UI/components)__: Components that help pages be complete, or other mixins to store repeating vue function and components.
 * __[Pages](control/UI/pages)__: Vue pages. I don't think further explanation is necessary.
 * __[Scripts](control/UI/scripts)__: Scripts that contain helping functions and classes - mainly the [APIShift JS library](control/UI/scripts/APIShift.js) is stored there to help us communicate and stay with the same standard and data as the back-end.
 * __[Styles](control/UI/style)__: All the css and other styling files of the control panel.
