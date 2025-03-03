# Registration site

## Introduction

This project was created to help with the registration of CoderDojo-The-Hague events. The project
implements a simple registration system.

A user (parent) has to register first. After registration a user can register participants 
(their children) for a workshop at the next event. If the workshop is full, the user can still
add a participant; the participant will be placed on a waiting list.

If the first workshop is full, the user may select a second backup workshop for the participant.

If a registration is cancelled, the system will check other participants and if a spot became
available in the workshop for the participant, the system will send a notification email to 
the user.

If there was a second backup workshop selected, the system will remove that workshop and again 
check all other participants for that workshop.

Administrator users can also access the back office. The back office offers pages to manage 
workshops, events and users. For events there is a page to check in participants. An administrator
can anonymize finished events: the name of participants is cleared and the reference to the user
is removed.

The project is setup so that it can be installed on a server next to an existing website.

## Requirements

- PHP 8.2+
- MySql 4.9+
- Composer

## Installation

- Copy the project to your server to the folder that contains the website root folder.
- Adjust the rights for the folders `/registration_site/tmp` and `/registration_site/logs`
  and their subfolders.
- Rename `/public_html` to correct name of your webroot. If the webroot already exists, copy the
  folder `registration` and its subfolders (inside `/public_html/`) to the existing webroot.
- Create a mysql database and run the script from the `/registration_site/sql/` folder.
- Change to `/registration_site/` folder and run `composer install`
- Rename `/registration_site/config/app_local-example.php` to 
  `/registration_site/config/app_local.php` and update the salt, database, email and other settings.
- Visit the webpage at `/registration/account/login` and register a new user.
- With a database management tool set the `administrator` field to `1` for the newly created user 
  in the `users` table.
 
## Technologies

The project uses the following technologies:
1. [CakePhp 5.x](https://cakephp.org/)
2. [Fontawesome 6.x (free version)](https://fontawesome.com/)
3. [Ultra Force Html helper](https://github.com/JoshaMunnik/uf-html-helpers/)
4. [Tinymce 5.x](https://www.tiny.cloud/)

The project uses local copies of the libraries.

## Database

The database uses uuid for the primary keys. 

To improve the stability the database defines foreign key relationships between the tables. 

There are two lookup/value tables: `languages` and `participant_types`. These tables use an integer
for primary keys.

A trigger is defined that will clear the `name` of a participant whenever the related `user_id` is
set to `null`.

## Languages

The implementation supports English and Dutch language.

To add a language perform the following steps:
- Add a new record to the `languages` table.
- Update `/registration_site/src/Model/Value/Language.php`.
- Create a folder with correct language code in `/registration_site/resources/locales/` folder.
- Add a new `default.po` to the created folder.
- Add email templates for the new language to the `/registration_site/templates/emails/html/` 
  folder. 

To create a `po` file, one can use [Poedit](https://poedit.net/).

## License

The project is licensed under the GPL-3.0 license. See the [gnu-gpl-v3.0.md](gnu-gpl-v3.0.md) file 
for more information.