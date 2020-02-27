<div align="center">
<img width="100px" src="https://gitlab.com/lesscomplexity/apishift/-/raw/master/images/DevLogo.png">

# APIShift Engine
</div>


The APIShift Engine is an API creator library with a control panel which purpose is to help build and deploy a fully functional API or Back-End for any type of system using a GUI system for:
 * Managing the sessions, its different states and how it changes overtime
 * Managing data structure in database using graphical representation of the data
 * Creating logical procedures using a graph-based UI to define procedures and attach them to different areas of the code/other procedures/lifecycle
 * Authorizing access for different states of session and requests (Will be available in next versions)
 * Analyzing the data passing through the database, session, access, procedures and code, then presenting it in the UI (Will be available in next versions)
 * Data warehousing by managing your data on different DB servers (Will be available in next versions)

## About
APIShift is an open-source PHP system that was designed by Sapir Shemer @lesscomplexity and its releases are maintained by [DevShift](https://devshift.biz). If you want to help and join the community, you are more than welcome to contribute. Just visit the [DevShift](https://devshift.biz) site or contact Sapir in any way you feel comfortable with: [LinkedIn](https://www.linkedin.com/in/sapir-shemer/), [Facebook](https://www.facebook.com/sapir.shemer).
Released under the MIT license.

### Meet Our Team
 * [Sapir Shemer](https://github.com/LessComplexity) as the architect.
 * [Eran Bodokh](https://github.com/Bodokh) as the Front-End lead.

## Installation
Clone the repository, upload it to your server, and then just visit the server through the web - the system will automatically redirect you to the installation page and will install the configurations and database by the data that you provide.

Notice that the [configurations file](machine/core/Configurations.php) should be writable, otherwise installation will return a permission denied error. And make sure to provide an existing schema on your DB host when installing.

### Requirements
 * Web server
 * PHP7+ with APCu enabled
 * MySQL

## Architecture
<div align="center">
<img width="50%" src="https://gitlab.com/lesscomplexity/apishift/-/raw/master/images/Architecture.png">
</div>

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

The system also defines 3 more concepts that are based and use the base concept to build up the logic, restrictions and analysis of the API, and as follows, define modular and scalable objects to handle it:
1. __Logic__: All the files and classes that make use of the session, database and core of the API to make the requests complete. All the logical elements are stored in the [models folder](machine/models), and also made viable by creating procedures using the system's UI and attaching them using triggers controllers, models or the lifecycle of the API.
    * [Procedure Management & Placement Class](machine/core/Task.php), Each task is made from different processes.
    * [Processes Management Class](machine/core/Process.php), Each process comes to life by combining operations.
    * [Operations Management Class](machine/core/Operation.php), Operations are made using pre-defined Rules or existing functions
    * [Rules Management Class](machine/core/Rule.php)
2. __Access__: Systems that are attaches to requests and controllers to validate the acces of a user to the request or the data that a client wishes to access. The Access management tools are based on Tasks & Processes and complete autorization processes using the session state which is made to store data that should indicate who is the client.
    * [Authorization Class](machine/core/Authorizer.php)
3. __Analysis__: Systems that are attached to different components of the system to accumulate usage data on the different components and the data that is transferred in them.

And around all of these definitions, the engine defines __Controllers__ as the combining part of the API. The controllers are objects that contain all the possible requests that can be made to an API. Each controller makes use of logic, and is surrounded by access and analysis methods to complete the full features of the API.

## Session management
Each API/server usually needs different types of sessions. One session can represent a regular user on your application and another can represent a premium user, each type of session has different permissions in your system - some can access a certain function/data and others don't. APIShift allows you to define different session states easily and then assign access rules on different functions and data using the states. The classes that manage the session options are the [core SessionState](machine/core/SessionState.php) and the [controller SessionState](machine/controller/SessionState.php).

The core SessionState contains the logic and functions that manage the session states, their updates, authorization and communication with the database. The controller SessionState provides a set of functions that a user can use to manipulate the session state - for example change the session on request and more. The controller uses the core object to make these requests come to life. Each session state has a state structure and value and children:

 * __Structure__: Keys and nested keys that make up the session objects.
 * __Values__: Indicator where to take the values from to fill in the structure - is it from the data or from database provided in the session change request? your choice.
 * __Children__: Children states are sub-states available on a certain state - they inherit and extend the structure and values of the parent state and use the same authorization process but with additional options or restrictions as you chose. For example a user session state can have a premium sub-session state that applies to premium users and provides more features in your application.

To add, modify and remove session states visit the "Session" tab in the control panel.

## Database management
The management system is present in the control panel and comes to life in your code. The graphical system represents the database structure in Object + Graph Model, Where each entity\object is refered to as an Item and each connection, is refered to as a Relation - which in itself acts as an Item (Allowing for relations between relations). It is translated into the relational model - SQL, in future versions also to different NoSQL models for increased integration.

The graphical framework, and even how it saves its representation doesn't refer to any primary keys that define the relations in the database it is saved upon. This allows for more flexibility when translating to different models and normalizing the database. The [DataModelManager](machine/core/DataModelManager.php), [Item](machine/core/Item.php) & [Relation](machine/core/Item.php) give you objects to work with the graphically represented model by translating it to the database's query language for you.

In later versions, you will be able to save your data on different DB servers, and APIShift will manage it for you - acting as a data warehouse. To add, modify and remove session states visit the "Database" tab in the control panel.

### UI Graph Terminology
When working in APIShift database, you create `Canvases`, where each canvas is a visual representation of database objects and how they are related & constructed. The system uses 2 main terms:
 * __Item__: A collection of keys and values, where keys are strings abd values are values attached to them as the user defines. In other words, an object in the DB.
 * __Relation__: A relation is an item that connects 2 or more items. Since a relation is also an item you can make relations between relations and also - this is what makes the terminology of the engine as a combination between [graph model semantics](https://en.wikipedia.org/wiki/Graph_database), [object model semantics](https://en.wikipedia.org/wiki/Object_model) and an [entity-relationship model](https://en.wikipedia.org/wiki/Entity%E2%80%93relationship_model).
    * *One-To-One*: For each instance of the relation, there can be no more than one instance for each of the parent item/s and related item/s (as you can see a relation can be derived from more that one item, referenced as parent item/s, to any other more than one item, references as related item/s).
    * *One-To-Many*: For each instance of the relation, there can be no more than one instance of parent items but as many related item instances as you like.
    * *Many-To-Many*: For each instance of the relation, there can as many parent and related items instances as you like.

This kind of model & semantics allows us to keep s single query language to access, cunstruct and normalize instances of the data by translating to queries for the relational model like SQL, document object models like mongodb and elastic search, graph models and more.

## Procedure Management
More will be added later

## Usage
After Using the APIShift to build an API or Back-End logic and controllers, you access the controllers/other parts and features of the system of in 2 different ways presented below.

### Requests (API)
Any request is actually a call to a method of a certain controller. You specify the controller and method in GET request form, and the data you want to pass to the method you pass as the method wants to recieve it - which is something you controll. Unless it's a method made by the system, where you should pass the data to the method in POST form. Usually to keep a standard we recommend you to pass the data in POST form even in your own methods. The url of the request should look similar to this:

```
https://example-site.com/machine/API.php?c=<ControllerName>&m=<MethodName>
```

### Hardcoded (Back-End)
APIShift also provides the [APIShift.php](machine/APIShift.php) file which loads the system whenever you include it. This way you can make PHP pages that include the APIShift system and construct the view on server-side instead of calling an API to recieve data and then construct the view (using JS for example). Then any call for a controller is recommended by using the `Authorizer::aurthorizeAndRun` method to ensure that an authorization process is done for the method call.

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

