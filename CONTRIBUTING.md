# Introduction
Want to contribute and help? Hell yeah! Anyone can do that, to become a contributor contact us at [DevShift](https://devshift.biz).
If you just have a new idea for change, you can post it in our site (_community ideas and codes section will open soon_), or comment your idea in the place in the code you want to implement the idea/change the current code to make it better. We will review it and if it is awesome as you are, then we will gladly add it and highlight you as the one who contributed to this part of the system.
We built this system for everyone to allow for faster deployment and development of products and projects, we hope that our project will help you and others greatly, and we would love to get help from you - so that you can help everyone else too.

Before contributing to APIShift, please take a moment to read the following guidelines:
 1. [Code of Conduct](CODE_OF_CONDUCT.md)
 2. [Readme file](README.md)
 3. [Framework Code Design](CODE_DESIGN.md)
 4. [Coding Standards](#coding-standards)
 5. [Git Workflow](#git-workflow)
 6. [Project Structure](#project-structure)

# Coding Standards
We use the [PSR-1](https://www.php-fig.org/psr/psr-1/) coding standard for PHP. Same rules apply to JS too (`camelCase` for functions & `StudlyCaps` for classes).
Variables can be `under_score` or `camelCase` both for PHP and JS, whatever suits better for that case. For example for a big variable name we would rather use camelCase because an under_score takes time to type, and we are all lazy af. Though lazy people should just use auto-complete which exists in every modern IDE today, if not, then you are probably not lazy - which is not good.

Another important part of contributing is keeping the accessibility standards of [WAI](https://www.w3.org/WAI/fundamentals/accessibility-principles/#standards) when developing the UI and even back-end of the system. We want the system to be accessible for everyone, so commits and propositions that don't keep the standards will be disgarded in the release.

When adding a new file to the system, don't forget to add the license notice on top of the file, for example (valid for any JS or PHP file, for other files use their style of commenting):

```
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author <Your-Full-Name/Username-Here>
 */
```

**Notice** that you should add an `@author` tag with your name or username right after, if you are contributing to an existing file, add to the end of the notice a `@contributor` tag with your name or username. When creating a new class or function, when you are not the author of the file, add the `@author` tag in comment on top of the class/function you created, in case you contributed to an existing function/class you are not the author of, then add the `@contributor`tag too.

# Git Workflow
When contributing you either help with an existing issue or work on your own idea. Issues are worked on the `development` branch, unless stated otherwise in the issue ticket. Your own ideas should be uploaded to a new branch `dev-{user_name}` where `user_name` is your user. After you finish your idea, just notify one of the maintainers and we will review it, and if it's good, we will send it to the `test` branch where your code will run through tests before uploading it to the `master` branch.

## NOTICE
Your database login information is stored in the [configurations file](machine/core/Configurations.php). to make sure you don't upload your personal information on commits to the server, please make sure to assume no changes in the configurations file:

```git
git update-index --assume-unchanged machince/core/Configurations.php
```

Moreover, when pulling new commits uploaded to the repo you will get an error saying that you have uncommited work and that you should stash it or something. Well, this happens because git is stupid sometimes (as we all have experienced it in some way or another) - Basically, when you have changes you marked as unchanged, and you changed something, then when pulling git will think that it is changed and will ask you to stash the changed or commit them - when you cannot do it because you marked them as unchanged - so you have 2 options here:
1. _Delete the original file_ - but keep the contents. Then pull and fill in your personal lost content.
2. _Mark it as changed_ - stash the shit out of the file, pull, pop the stash, then mark it as unchanged again.

I prefer option 1.

# Things we need help with
 * Perfecting performance in our system's [core files](machine/core)
 * Building a stable system for handling data warehousing - in case a user decides to use more than one DB for his application
    * [DatabaseManager](machine/core/DatabaseManager.php)
    * [DataModelManager](machine/core/DataModelManager.php) - The most critical part when it comes to managing more than one DB
    * [Item](machine/core/Item.php)
    * [Relation](machine/core/Relation.php)
 * Building and integrating a modular and configurable system for [analyzing data and usage](machine/core/Analyzer.php) on the system. And integrating it into the UI of the [control panel](control) and the whole core files so that it will work "under the surface".
 * Creative ideas that can be related and help the users of the system are more than welcome.

