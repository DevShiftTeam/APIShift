<div align="center">
<img width="100px" src="https://raw.githubusercontent.com/DevShiftTeam/APIShift/master/images/apishift-logo.png">

# APIShift Architecture And Design
</div>

This document organizes the different components, sub-components and their properties that make up the engine. The goal of this document is to provide both a high level overview of the architecture of the system and guidelines for the functionality and algorithmics behind the engine practically. All new systems and components first need to be integrated in the code design before going into development stages to keep a clean, understandable flow of development, organization and efficiency. It is always better to initially design the components and overall system before developing, it helps others follow up with the same ideas and standards when developing and contributing to the system.

The semantics of this architecture document will follow the definitions mentioned in [Architectural Styles and the Design of Network-based Software Architectures. Doctoral dissertation by Roy Thomas Fielding, University of California, Irvine, 2000. Chapter 1](https://www.ics.uci.edu/~fielding/pubs/dissertation/software_arch.htm).

# Table of contents
- [APIShift Architecture And Design](#apishift-architecture-and-design)
- [Table of contents](#table-of-contents)
- [Architecture Overview](#architecture-overview)
  - [Architectural Style](#architectural-style)
    - [Definitions](#definitions)
  - [Component Classifications](#component-classifications)
    - [Base Components: 1st Lvl Abstraction](#base-components-1st-lvl-abstraction)
    - [Base Components: 2nd Lvl Abstraction](#base-components-2nd-lvl-abstraction)
  - [Request Workflow](#request-workflow)
- [Architectural Elements](#architectural-elements)
  - [Data](#data)
    - [Meta-data of data](#meta-data-of-data)
      - [Database](#database)
      - [Memory](#memory)
    - [Session States](#session-states)
      - [Database](#database-1)
      - [Memory](#memory-1)
    - [Tasks](#tasks)
    - [Languages & Translation](#languages--translation)
    - [Items](#items)
  - [Cache](#cache)
    - [Data](#data-1)
    - [Interface](#interface)
  - [Data Manager](#data-manager)
      - [Data Access Optimization](#data-access-optimization)
    - [Interface](#interface-1)
  - [Session States](#session-states-1)
    - [Core Interface](#core-interface)
    - [Admin Controller Interface](#admin-controller-interface)
    - [Main Controller Interface](#main-controller-interface)
  - [Database](#database-2)
    - [Database Manager](#database-manager)
      - [Data](#data-2)
      - [Core Interface](#core-interface-1)
    - [DataModel](#datamodel)
      - [Data](#data-3)
      - [Core Interface](#core-interface-2)
      - [Controller Interface](#controller-interface)
    - [Item](#item)
      - [Data](#data-4)
      - [Core Interface](#core-interface-3)
  - [Task](#task)
    - [Data](#data-5)
    - [Core Interface](#core-interface-4)
    - [Controller Interface](#controller-interface-1)
  - [Process](#process)
    - [Data](#data-6)
    - [Core Interface](#core-interface-5)
    - [Controller Interface](#controller-interface-2)
  - [Access](#access)
    - [Data](#data-7)
    - [Core Interface](#core-interface-6)
    - [Controller Interface](#controller-interface-3)
  - [Analysis](#analysis)
    - [Data](#data-8)
    - [Core Interface](#core-interface-7)
    - [Controller Interface](#controller-interface-4)
  - [Extensions](#extensions)
    - [Data](#data-9)
    - [Core Interface](#core-interface-8)
    - [Controller Interface](#controller-interface-5)
- [Project Structure](#project-structure)
  - [Back-End](#back-end)
  - [Control panel UI](#control-panel-ui)

# Architecture Overview
In this section we will give an overview of the definitions, components, connections between them and the data elements of the overall architecture. APIShift, through its architecture strives to not be bound to a specific API architecture (e.g. REST, SOAP), thus making it an abstract API that can be configured to follow any netwrok-based software architecture you want.

We also want to keep our system as clean as possible from other complex technologies so that anyone can start working with it, and add whatever packages and technologies they want. You can use composer to add Symfony packages, Doctrine and more on your own.

## Architectural Style
An architectural style, is a set of restrictions that guide how we define architectural elements and relate between them in ways that satisfy the given style.

Our style is a component based architectural style, that follows the definitions presented in the [Component Classifications](#component-classifications) section. Meaning that any component in our architecture will belong to a specific classification defined in that section. We strive to follow a data-oriented design, where the data is visible to all the components, which requires no visibility notations on data like public, protected or private, it makes the architecture more clean, and since data is shared and no private data is needed, then no internal component states about data need to be saved - which in a lot of practices has minimized the amount of data used throughout the program. Every component acts as a system - a collection of functions. Some data will be saved in different components, but will remain public. Some data elements will be stored in cache and loaded into runtime when called, if no cache system is present, then they will automatically be loaded into runtime - therefore a cache system like APCu, Redis or Memcache is recommended to increase speed.

Each component will be divided in a Core-Model-Controller manner, such that: Each component's functionallity that is visible, and can be triggered by an end-user will be referred by a `Controller` component. And any functionallity that is not visible to the end-user will be referred by a `Model` component. User-made components may choose any style he desires for `Model` components, but must create `Controller` components on top of them when making it visible to the end-user through the API. `Core` components are basically `Model` components  that define the overall system's workflow and give user-made components the basic functionallity needed to integrate with the system's workflow and features.

This style is not very restrictive in its definitions, as it only divides whatever component given into which part of it define the visible end-user interface of the API (`Controller`) and the other part as a different component/architecture (`Model`/`Core`). The style is intended to be modular, maintainable and extendable to provide the ability to expand the base system into any kind of API by developing your own `Controllers`/`Models`. The base components (`Core`) are developed with functionallity to help you maintain a managed flow of procedures, requests and analysis on the overall system.

### Definitions
The system uses the following syntactic terms, which are presented as architectural components. The purpose of those definitions is to create a template for passing different type of data elements between components, such that architectually (and practically) components won't need to worry if the data came in from an array or database.

 * __Data Entry__: A data entry is any varaible, constant, key or table cell.
 * __Data Sources__: Data sources are sources of data entries: arrays, tables, documents, items & relation (an Item and a Relation are components that process data elements of tables and documents under a unified definition to create a single query language that can access both types of data, allowing for integration and fast transitions from [relational](https://en.wikipedia.org/wiki/Relational_database) to [document](https://en.wikipedia.org/wiki/Document-oriented_database) models. We will review the Item and Relation components later on).
 * __Procedural Connections__: A connection between data entries, sources or other procedural connection and processing elements (e.g. functions) - each procedural connection represents a function operation in run-time on the connected data. This defintion represents a concept that the APIShift framework uses to run, interpret and create functionallity. Later on this concept will be used to turn graphical diagrams made using the framework into run-time elements of your system.

## Component Classifications
<div align="center">
<img width="50%" src="https://gitlab.com/lesscomplexity/apishift/-/raw/master/images/Architecture.png">
</div>

The diagram above shows the different classifications each component can  belong to. The purpose of these classification is to create an abstraction above the architectural components that can, in theory, classify any type of API or server components into these definitions for providing a scalable and modular system to build any type of API/Server. These classification are built around the idea that anyone can define & add it's own components, and classify them according to this model. Then the base components are created with definitions and connections that abide this classification, and the ability to expand their functionallity and architecture by combining other components in the same classification, to fit the desired architecture of any API/server the developer wishes to make.

### Base Components: 1st Lvl Abstraction
The system defines 3 main base concepts that give the basic functionallity to configure metadata, and have access to long and short term memory.
1. __Core Configurations__: Holds the framework's configurations. For example, credentials for connecting to the main database, and other hardcoded metadata. Practically the core file is defined during system installation. 
    * [Core Configurations Class](machine/core/Configurations.php)
2. __Database__: The components that manage and communicate with the database to access the 'long-term memory' of the framework from code and translate between different database models.
    * [PDO Objects Collection Class](machine/core/DataModelManager.php)
    * [Data Structure Manager Class](machine/core/DataModelManager.php)
    * [Item Class](machine/core/Item.php)
    * [Relation Class](machine/core/Relation.php)
3. __Session__: The components that manage the data elements and data strcuture and how they change per each session. Practically, in PHP terms, each session at any point in run-time has PHP array (key-value) defining the structure and data of the session stored in the `$_SESSION` variable, which our system refers to as the current session state. Can be considered as a 'short-term memory' of an API or BE.
    * [Session State Management Class](machine/core/SessionState.php)

### Base Components: 2nd Lvl Abstraction
The system also defines 3 higher level concepts that are using the base components to build up the logic, restrictions and analysis behind the requests of an API.
1. __Logic/Models__: Components that make use of the session, database and core of the API to provide `Model` functionallity. All these components are stored in the [models folder](machine/models), and later can also be made by creating procedures using the system's UI and attaching them using triggers  to other `Controllers`, `Models` and the overall lifecycle of the API.
    * [Task Management Class](machine/core/Task.php), Each task manages a collection of processes.
    * [Processes Management Class](machine/core/Process.php), Each process in a handler of procedural connections, and holds the functionallity to compile them in run-time.
2. __Access__: Components that provide functionallity to attaches validation procedures at run-time on the data/functionallity that an end-user wants to access/trigger. The access management tools are based on Tasks & Processes and complete autorizations using the session state.
    * [Authorization Class](machine/core/Authorizer.php)
3. __Analysis__: Components that are connected to other components of the system to accumulate usage data on the different components and the data that is transferred in the system to illustrate a analytic image of the system in terms of performance, access and usage.

Around all of these definitions, the engine defines __Controllers__ as the highest level of abstraction of an API it is concerned with. The controllers are components that contain all the possible requests that make up an API. Each controller makes use of logic, and is surrounded by access and analysis components to complete the full features of the API.

## Request Workflow
The APIShift framework provides a general workflow for each request, with an ecosystem that allows you to authenticate and analyze requests. This workflow is configurable by the components and features of the system.

1. **Connect to main DB**: First and foremost, APIShift establishes a connection with the main DB, which contains the metadata needed to access to the cache, the session, run validations and authentcations on the server.
2. **Load default cache data**: The APIShift system caches metadata about the different sessions, database structure and more to speed up queries and operations. if cache data is loaded or a cache system is not enabled, the framework skips this phase.
3. **Load default session**: Loads the session, its structure and values. Handles session construction and destruction automatically by the given timeouts.
4. **Validate Request**: Validates the format of a request. Checks if request exists as a task (tasks will be discussed later), a controller method or an extension feature.
5. **Authorize Request**: Loads and runs tasks that are attached as the authorization process of the request, and by doing so confirm that the request can be accessed by the requesting client.
6. **Run Request**: If all the previous steps are completed successfully then the controller method/task/extension feature requested by the user will be called.

Part 1 to 3 of this workflow is implemented by the [APIShift.php](machine/APIShift.php) file, and the other part 4 to 6 is implemented by the [API.php](machine/API.php) file. This separation is made so that if a developer wishes to implement server-side rendering or his own components using the framework's features, he can simply include the APIShift.php file, while the API.php file serves at the head file from which requests to the API controllers are made.

# Architectural Elements
This title will discuss the different components, connectors and data elements of the famework, their features, interfaces and responsibility. Each element will be desribed by the data elements it affects, which will be discussed in the [**Data**](#data) subtitle, and will also be decribed by their interfaces - usually the model/core interface and controller interface in their own **\<Some> Interface** subtitle.

## Data
Since we use a data-oriented approach, the guiding elements of our architecture are the data elements themselves. Though our components are separated based on classification which do not relay on data, but the components themselves act as systems working by the program's data.

### Meta-data of data
Data can come from a lot of sources, e.g. databases and arrays. To access data better, we keep a meta-data representation of the data in the system, this way other components can share and work with data elements in the system more effectively. As we have defined before, our model uses data sources and data entries notations.

#### Database
* ___data_sources___ (SQL) - Collection of different data sources that the system uses at run-time for different operations.
  * _id_ - Identification.
  * _name_ - Name of the data source.
  * _type_ - ID of the type of a data source.
* ___data_source_types___ (SQL) - An id-name table representing the different types of data sources: arrays, tables, items/relations (will be discussed later), static_class (a class holding static variables), class_instance (a class instance).
* ___data_entries___ (SQL) - Collection of the data entries used by the system.
  * _id_ - Identification.
  * _name_ - Name of the data entry.
  * _type_ - ID of the type of a data entry.
  * _source_ - The source which this entry belong to if present.
* ___data_entry_types___ (SQL) - An id-name table representing the types of entries, can be either of: array_key, variable, constant (In case of constant the name is considered the value), table_cell.

#### Memory
The database tables are loaded into cache when the system does its first run, if no cache system is present then the data is loaded during runtime from the database - which requires to make a query, thus effects the performance of the system. That's why we recommend using a cache system. When a value of a data entry is called, it is loaded into run-time using the meta-data about the entry. The system responsible for getting data based on meta-data is [DataManager](#data-manager).

### Session States
Sessions store data about a client that is accessible throughout the different requests between the client and the server. A simple analogy will be to say that it's somehow like server-side cookies. Sessions are great tools to store a certain "state" about a client when exchanging requests, indicating our program who the client is - is it an admin? a player in our app? a premium user maybe? all these different clients have different restrictions on the functionallity and data they can access.

Each session state has a state structure, indicating how the data about the state is saved, it also needs to know which data entries to take value from to fill them, and who are their children that inherit their properties:

 * __Structure__: A key-value store, in our case in a PHP array.
 * __Values__: The meta-data about where we get the values in a session state structure to fill in the structure at run-time.
 * __Children__: Children states inherit and extend the structure and values of the parent state and use the same authorization process but with additional options or restrictions added by the developer. For example a "user" session state can have a "premium" child state that applies to premium users and provides access to more features in your application.

#### Database
 * ___session_state___ (SQL) - Used to encapsulate and separate session data structures at a given state for different types of sessions.
   * _id_ - Identifier of the state.
   * _name_ - Name identifying the state.
   * _inactive_timeout_ - Timeout untill system disposes of the session user when not active.
   * _active_timeout_ - Timeout until system disposes the session since whether active or not.
   * _auth_task_ - Authorization task running the procedure when a user requests a change into this state. A task can be your own function, more on tasks will be discussed later.
   * _parent_ - id of the parent session.
 * ___session_state_structures___ (SQL) - Defines the key-value store structure of the session in run-time.
   * _id_ - Personal identification.
   * _state_ - Identification of state this entry belongs to.
   * _key_ - Name of the value the entry is holding.
   * _entry_ - Identification of the data entry which the value coppied from with when state is changed.
   * _parent_ - id of the parent entry.

#### Memory
The current session state is loaded into the `$_SESSION` array in PHP. The meta-data about the different states is stored in the cache under the key `session_states`. If no cache system is present, when when accessing data about session states, it will be loaded directly from the database.

### Tasks

### Languages & Translation

### Items

## Cache
The cache is an interface that provides a handler for cache systems that can work with different caches such as Memcached, Redis and APCU, while hiding the implementation details behind a simple get-set interface. The cache system is expressed in the [machine/core/CacheManager.php](machine/core/CacheManager.php) class.

### Data
> No Data

### [Interface](machine/core/CacheManager.php)
* `public addSystem($system_type, $name, $credentials)` Adds a new cache system (of type APCU, MemCached, Redis) to collection. This function will be available later for extendability and for the framework to be able to integrate into larger projects.
* `private initialize()` Initializaes the cache system, exits on error.
* `public loadDefaults()` Loads the database tables that are used for the main framework calls to not spend time requesting data from the database for core operations that happen frequently.
* `public set($key, $value, $ttl, $system_name)` Set/modify a variable in cache. System name is by default "main" which refers to the main cache system defined in the installation.
* `public get($key, $system_name)` Get a variable by name from cache. System name is by default "main" which refers to the main cache system defined in the installation.
* `public exists($key, $system_name)` Returns true if a key exists. System name is by default "main" which refers to the main cache system defined in the installation.
* `public getTable($table_name, $ttl, $system_name)` Load table data into cache. System name is by default "main" which refers to the main cache system defined in the installation.
* `public getFromTable($table_name, $id, $ttl, $system_name)` Store row from DB to cache. System name is by default "main" which refers to the main cache system defined in the installation.

## [Data Manager](machine/core/DataManager.php)
The APIShift frameworks, as defined in the [Definitions](#definitions) section, expresses what is called data entries and data sources, where a source referes to a "pool" of data entries, and an entry refers to a value in our system. Since a source or an entry can be expressed by different mechanisms (e.g. a source can be an array or a database table), the Data Manager provides a simple interface to access and read data entries and sources in the system regardless of their type or origin while hiding the implementation details from the user. This system will be used to simplify work when defining processes and tasks in the system.

The ___data_entry_types___ and ___data_source_types___ tables are loaded to cache by the `loadDefaults()` in the cache manager, to make types access faster during run-time.

#### Data Access Optimization
The Data Manager component has 3 levels of access to variables & data when accessing from the data manager:
* **Runtime**: After a variable has been loaded, it is saved as a PHP array (map) that maps metadata about the data entries and sources that have been requested, this way the other time they will called again in the same request, the system will take them from this PHP array which exists at run-time - meaning it is accessing data on the same process.
* **Cache**: When metadata about the variable has been loaded once, it's metadata will be saved in cache for faster access later. This means that the calls require an IPC communication between processes, which takes a little more time than data on the same process.
* **Database**: The static way that metadata and data about the data entries and sources is stored, this is the the last station if the data was not found in runtime or cache.

### [Interface](machine/core/DataManager.php)
* `private uploadEntryToCacheAndRuntime($id)` Uploads an entry meta data to cache and run-time. An upload to run-time is happening so that other calls for getting or setting the entry will be faster.
* `public getEntryData($id)` Returns the data about an entry, such as its type, source and name.
* `public getEntryValue($id, $where_query_attrib)` Returns the value an entry holds. In case of a table cell, the system will use the where query attributes array to make a where clause, such that the keys will be the column names and values are the comparison values.
* `public setEntryValue($id, $value)` Modifies the value of a given entry.
* `public addEntry($name, $type, $source)` Adds a new entry to DB.
* `public addSource($name, $type)` Adds a new source to DB.
* `public removeEntry($id)` Removes an entry from DB.
* `public removeSource($id)` Removes a source from DB.

## Session States
APIShift allows you to define different session states easily and then assign access rules by these states to data, controllers and methods. The classes that manage the session states are the [core of the SessionState](machine/core/SessionState.php). The [controller interface SessionState](machine/controller/SessionState.php) allows for managing the session through API requests.

The core of the SessionState contains the logic and functions that manage the session states, their updates, authorization and communication with the database. The controller of the SessionState provides a interface that a user can use to manipulate the session state - for example change the session on a given request to indicate a login or logout, and more.

To manage session states in your API visit the "Session" tab in the control panel.

### [Core Interface](machine/core/SessionState.php)
This interface's functions are all public.
* `loadDefaults()` Initializes the session, and runs timeout checks to automate session creation and destruction at run-time.
* `changeState($name)` Changes the current states into a new state. Automatically runs the authentication process attached to the session.
* `getSessionState()` Returns the ID of the current session state.
* `getIDFromName($name)` Returns the ID of the session name given.

### [Admin Controller Interface](machine/controllers/admin/SessionState.php)
This interface's functions are all public.
* `getAllSessionStates()` Returns all the sessions states.
* `add()` Add a new session state and its structure.
* `update()` Update existing session and its structure.
* `remove()` Remove a session state and its structure.

### [Main Controller Interface](machine/controllers/main/SessionState.php)
This interface's functions are all public.
* `changeState()` Changes the session state to the one required in the post request.
* `getCurrentState()` Returns the current session state.

## Database
The APIShift framework persents three types of database components to manage the long-term data of your app. The DatabaseManager provides an interface to make queries to different databases - it also keeps and manages the collection of database connections the system uses. The Item component provides a set of tools to work with database data with an abstraction layer that the APIShift framework uses to simplify data management in databases on different types of databases with a simple interface. This abstraction layer presents Items, which are data elements, and Relations, that express relations between Items, and are also considered items by themselves. Behind the scenes the system will optimize and map the data in a normalized manner to the database. The last type of database component the system has is the DataModel, which is an interface and controller for creating, managing and ordering items and relations in our system.

### Database Manager
The purpose of the Database Manager is to provide an interface for working with multiple databases. Right now it is build to work with SQL databases, hopefully later will also integrate with mongodb and other types of databases. It has a pool of database servers mapped by keys. The "main" key is saved for the database holding the core databases of the APIShift framework. The Database Manager in general holds all your connection objects and provides a unified interface to use them.

#### Data
 * `private $connections_set[]` A PHP array where the keys hold the connection objects to other databases.

#### [Core Interface](machine/core/DatabaseManager.php)
 * `public static addConnection($name, $type, $credentials)` Adds a new connection to the pool of connections the DatabaseManager holds. Also starts the connection if no connection object exists.
 * `public static getInstance($name)` Returns the connection object associated with the name.
 * `public static closeConnection($name)` Closes the matching connection.
 * `public static query($name, $query, $data)` Runs a query with the data attached on the connection specified.

### DataModel
When working with database components of the APIShift, you create `Canvases`, where each canvas is a graph representation of database elements and how they are related & constructed. The system uses these terms/components:
 * __Item__: An abstract components that is presented as a collection of keys and values, that represent data elements stored in the DB.
 * __Relation__: A relation is an item that makes and abstract connection between 2 or more items. Since a relation is also an item you can make relations between relations - this is what makes the terminology of the engine as a combination between [graph model semantics](https://en.wikipedia.org/wiki/Graph_database), [object model semantics](https://en.wikipedia.org/wiki/Object_model) and an [entity-relationship model](https://en.wikipedia.org/wiki/Entity%E2%80%93relationship_model). Each relation is one of those types:
    * *One-To-One*: For each instance of the relation, there can be no more than one instance for each of the parent item/s and related item/s (as you can see a relation can be derived from more that one item, referenced as parent item/s, to any other more than one item, references as related item/s).
    * *One-To-Many*: For each instance of the relation, there can be no more than one instance of parent items but as many related item instances as you like.
    * *Many-To-Many*: For each instance of the relation, there can as many parents and related item instances as you like.
 * __Group__: Items & Relations can be grouped together - grouping abstracts items and relations as a single element which helps developers create relations between multiple items in a single connection, it is made for better user experience. Practically, when a relation is pointing to a group or from a group, the table representing the relation will have a `from_type` and `to_type` respectively, which are foreign keys related to a table holding the types of items in the group.
 * __Type__: Each Item & Relation can have types - for example the users item can be of type admin, premium or regular - this feature is also for better user experience, as developers can view and manage types in system queries, and even relate only specific types of items, which offers more flexibility.

These kind of defintions and components allow us to keep a single query language to access, construct and normalize data elements in a database of any type (SQL and NoSQL structures like mongodb, graphQL and more).

#### Data
More will be added later

#### [Core Interface](machine/core/DataModel.php)
More will be added later

#### [Controller Interface](machine/controllers/admin/DataModel.php)
More will be added later

### Item
More will be added later

#### Data
More will be added later

#### [Core Interface](machine/core/Item.php)
More will be added later

## Task
More will be added later

### Data
More will be added later

### Core Interface
More will be added later

### Controller Interface
More will be added later

## Process
More will be added later

### Data
* ___processes___ (SQL) - id-name Table containing the list of names processes in the system.
* ___connections___ (SQL) - Each process has a list of connections defined in the ___process_connections___ table. A connection defines a procedure between data elements.
  * _id_ - Identification.
  * _connection_type_ - A connection defines a procedures, this procedure can come as another Process, Task, Function, a rule (a set of pre-made directives) or information transfer.
  * _name_ - Name of connection (information transfer ref, process, task, function, rule).
  * _from_type_ - 
  * _from_ - 
  * _to_type_ - 
  * _to_ - 
* ___connection_types___ (SQL) - 
* ___connection_node_types___ (SQL) - 

### Core Interface
More will be added later

### Controller Interface
More will be added later

## Access
More will be added later

### Data
More will be added later

### Core Interface
More will be added later

### Controller Interface
More will be added later

## Analysis
More will be added later

### Data
More will be added later

### Core Interface
More will be added later

### Controller Interface
More will be added later

## Extensions
More will be added later

### Data
More will be added later

### Core Interface
More will be added later

### Controller Interface
More will be added later

# Project Structure
Here we will review the filesystem structure of the APIShift framework before getting into specifics.

## Back-End
The back-end of the system is in the [machine folder](machine/) where you can find 4 different folders:
 * __[Core folder](machine/core)__: The folder containing all the files defining the basic core, database and session components of the system. The core files are classes that make up the main workflow of the system and contain methods to work with the system's main features. Any new core feature will be integrated into files in this folder or as new files. Everything in the core folder should be under the namespace `APIShift\Core`.
 * __[Models folder](machine/models)__: Contains any classes that are used to make logical and database specific operations outside the scope of the system - made for users of the system. If you are contributing then you will probably touch every folder besides this one, unless you are adding features for managing extensions or helper functions, as the extensions management class and helper class exist in this folder. Everything in the models folder should be under the namespace `APIShift\Models`.
 * __[Controllers folder](machine/controllers)__: The controller classes hold the methods that define the requests that can be made to the API. Each controller method validates the request and then runs through the necessary models and core files to complete the request. The `Authorizer` handles the authorization and access rules for user-made methods - but if you are making methods for the control panel and developers of the system make sure to use the `Authorizer::authorizeState()` function, when no parameters are provided, it exits if the user is not in admin state - which is set only when the developer logs into the framework. Everything in the controllers folder should be under the namespace `APIShift\Controllers`.
 * __[Data folder](machine/data)__: Files that store and provide streams of data elements to system components, without modifying the data. For example, ini and sql files that load and install the system and other helpful files for configurations and more.

And there are 2 main files in the back-end:
 * **[APIShift](machine/APIShift.php)**: A simple script that loads the autoloader of the system - which knows how to interpret the framework's namespaces with ease, loads the session and starts the main connection with the database.
 * **[API](machine/API.php)**: This file manages the API workflows for each request, it first calls the APIShift file to load the system, then it validates the request, calls the authorizer to authorize the request, and runs the desired controller & method - the only way to make API requests to any controller should only be from this file, as it integrates the authorization process for you and other necessary check. If you build your views from PHP files than you need to include the APIShift file as explained in the [readme](README.md)'s usage section.

## Control panel UI
The second most important structure to understand is the UI of the control panel which sits in the [control folder](control/). The control panel has a [UI folder](control/UI/) that contains all the UI components and an [index file](control/index.html) that integrates them together to make the single-page application of the control panel. The UI folder contains 4 sub-folder:

 * __[Components](control/UI/components)__: Components that help pages be complete, or other mixins to store repeating vue function and components.
 * __[Pages](control/UI/pages)__: Vue pages. I don't think further explanation is necessary.
 * __[Scripts](control/UI/scripts)__: Scripts that contain helping functions and classes - mainly the [APIShift JS library](control/UI/scripts/APIShift.js) is stored there to help us communicate and stay with the same standard and data as the back-end.
 * __[Styles](control/UI/style)__: All the css and other styling files of the control panel.
