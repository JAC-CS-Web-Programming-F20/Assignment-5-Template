# Assignment 5 - Sessions 🍪

- 💯**Worth**: 15%
- 📅**Due**: December 6, 2020 @ 23:59
- 🙅🏽‍**Penalty**: Late submissions lose 10% per day to a maximum of 3 days. Nothing is accepted after 3 days and a grade of 0% will be given.

## 🎯 Objectives

- **Authenticate** users of the application using PHP sessions.
- **Restrict** actions and access to UI elements based on session data.

## 📥 Submission

Since we'll be using [Git](https://git-scm.com/) and [GitHub Classroom](https://classroom.github.com/) for this assignment, all you need to do to submit is to commit and push your code to the repository. Over the course of working on the assignment, you should be committing and pushing as you go. I will simply grade the last commit that was made before the assignment deadline.

1. `git status` to see everything that has changed since your last commit.
2. `git add .` to stage all the changed files. Instead of `.`, you can also specify one file or folder.
3. `git status` to make sure all the staged files are the ones you wanted.
4. `git commit -m "Your commit message here."`
5. `git push`
6. `git status` to make sure everything is good.

### 💯 Grade Distribution

- `ControllerTests` = **25%**
- `RouterTests` = **25%**
- `BrowserTests` = **50%**

## 🔨 Setup

1. Attach VSCode to the PHP container. Make sure that inside the container, you're currently in `/var/www/html/Assignments`.
2. Follow the instructions from A1 to clone the repo if you're not sure how.
3. You should now have a folder inside `Assignments` called `assignment-5-githubusername`.
4. Change the `PUBLIC_PATH` in `src/Helpers/Url.php` so that it reflects your folder structure and has your GitHub username in the path.
5. Create the folders: `src/Controllers`, `src/Models`, and `src/Views`.
6. Copy all of the controllers from your A4 into `src/Controllers`.
7. Copy all of the models from your A4 into `src/Models`.
8. Copy all of the views from your A4 into `src/Views`.
9. Update all the namespaces in all the files you copied over to `AssignmentFive`.

## 🖋️ Description

In A1, we created the 4 main models (`User`, `Category`, `Post`, and `Comment`) that are in charge of talking to the database. The models are based on the entities from the ERD which can be found in the A1 specs.

In A2, we implemented the `Router` that handles the web requests/responses and instantiates a `Controller`. We also implemented the `Controller` which takes care of deciding which model method to call.

In A3, we implemented the **error-handling** aspect of our application by **throwing and catching exceptions**. If the `Model` threw an `Exception`, it was to be caught by the `Controller`, and thrown up to the `Router`. The `Router` then populated the `Response` message using the error from the `Exception`.

In A4, we implemented the **views** of our application using the [Plates PHP templating engine](https://platesphp.com/v3).

In this assignment, we will implement **session management** to allow users to log into our application:

- A user who is not logged in may only use the site as *read-only*. This means they cannot create/update/delete any categories/posts/comments. The only thing they can create is a new user by registering.
- A user who has registered can log in using the login form. After they are successfully logged in, they may create categories/posts/comments. In addition to this, they may update/delete any categories/posts/comments they have created.
- Before creating anything, we need to check if a user is currently logged in.
- Before updating/deleting anything, we need to check if:
  - a user is currently logged in;
  - the currently logged in user's ID matches the user ID of the thing they're trying to update/delete.
- A user may log out which should destroy their session on the server.

## 🗺️ Routes

| Request Method | Query String          | Action                            | Redirects/Template      | Description                                          |
| -------------- | --------------------- | --------------------------------- | ----------------------- | ---------------------------------------------------- |
| `GET`          | `/`                   | `HomeController::home`            | `HomeView.php`          | Display the homepage.                                |
| `ANY`          | `/{garbage}`          | `ErrorController::error`          | `ErrorView.php`         | Display a 404 error page.                            |
| `GET`          | `/auth/register`      | `AuthController::getRegisterForm` | `User/NewFormView.php`  | Display a form to register a new user.               |
| `GET`          | `/auth/login`         | `AuthController::getLoginForm`    | `LoginFormView.php`     | Display a form to log in a user.                     |
| `POST`         | `/auth/login`         | `AuthController::logIn`           | `/user/{id}`            | Log in a user by setting a session variable.         |
| `GET`          | `/auth/logout`        | `AuthController::logout`          | `/`                     | Log out the user by destroying the session.          |
| `POST`         | `/user`               | `UserController::new`             | `/auth/login`           | Register a user.                                     |
| `GET`          | `/user/{id}`          | `UserController::show`            | `User/ShowView.php`     | Display a user's profile where they can edit/delete. |
| `PUT`          | `/user/{id}`          | `UserController::edit`            | `/user/{id}`            | Edit a user's profile.                               |
| `DELETE`       | `/user/{id}`          | `UserController::destroy`         | `/user/{id}`            | Deactivate a user's profile.                         |
| `POST`         | `/category`           | `CategoryController::new`         | `/`                     | Create a new category.                               |
| `GET`          | `/category/{id}`      | `CategoryController::show`        | `Category/ShowView.php` | Display all posts in a category.                     |
| `GET`          | `/category/{id}/edit` | `CategoryController::getEditForm` | `Category/EditView.php` | Display a form to edit a category.                   |
| `PUT`          | `/category/{id}`      | `CategoryController::edit`        | `/category/{id}`        | Edit category title/description.                     |
| `DELETE`       | `/category/{id}`      | `CategoryController::destroy`     | `/`                     | Deactivate a category.                               |
| `POST`         | `/post`               | `PostController::new`             | `/category/{id}`        | Create new post.                                     |
| `GET`          | `/post/{id}`          | `PostController::show`            | `Post/ShowView.php`     | Display a post's details and comments.               |
| `GET`          | `/post/{id}/edit`     | `PostController::getEditForm`     | `Post/EditView.php`     | Display a form to edit a post.                       |
| `PUT`          | `/post/{id}`          | `PostController::edit`            | `/post/{id}`            | Edit contents of text post.                          |
| `DELETE`       | `/post/{id}`          | `PostController::destroy`         | `/post/{id}`            | Deactivate a post.                                   |
| `POST`         | `/comment`            | `CommentController::new`          | `/post/{id}`            | Create a new comment.                                |
| `GET`          | `/comment/{id}`       | `CommentController::show`         | `Comment/ShowView.php`  | Display a comment along with its replies.            |
| `GET`          | `/comment/{id}/edit`  | `CommentController::getEditForm`  | `Comment/EditView.php`  | Display a form to edit a comment.                    |
| `PUT`          | `/comment/{id}`       | `CommentController::edit`         | `/post/{id}`            | Edit the contents of a comment.                      |
| `DELETE`       | `/comment/{id}`       | `CommentController::destroy`      | `/post/{id}`            | Deactivate a comment.                                |

## 🧪 Tests

It is **highly recommended** that you do the tests in the following order:

1. `tests/ControllerTests/AuthControllerTest.php`.
2. Work on fixing all of the (now failing) **controller tests** since we need to check for valid login before doing stuff.
3. Work on fixing all of the (now failing) **router tests** since we need to check for valid login before doing stuff.
4. Finish by passing the **browser tests** to check for valid login before doing stuff.

> 🤔 Doing stuff == creating/updating/deleting the various entities.

### 🎥 [Test Suite Video](https://youtu.be/xAsjk0THxnM)

The test code itself serves as a guide for you to create your views as they will tell you what elements on the page it expects. To aid you further, I've recorded a slowed down run of all the browser tests which can be found [here](https://youtu.be/xAsjk0THxnM). This will enable you to see my interpretation of the pages, and how they look and function.
