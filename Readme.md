# ZuraTech takehome Full stack Web developer assignment

Thank you for taking the time to complete our take-home test. This assessment is designed to simulate some of the tasks you would be working on in this role. It allows you to showcase your skills in PHP, WordPress, JavaScript, and CSS/SASS.

We estimate this test will take 2-4 hours. Please don't spend more than 4 hours on it; we respect your time. We're more interested in your approach and the quality of your work than in a perfect, feature-complete implementation.

### What We're Looking For:
* Clean, readable, and maintainable code.
* Good architectural decisions and an understanding of best practices. (Please be ready to answer questions about decisions you made)
* Attention to detail in implementing the requirements.
* A logical git commit history.
* Testing strategy and testing automation.
* Ease of deployment provided solution.

### Setup & Submission:
* We have provided a starter package with API, Wordpress strarted and a static site page.
* Please initialize a Git repository and commit your progress as you work through the tasks.
* When you're finished, please provide us with a link to your private GitHub/Bitbucket repository (and invite us as collaborators)
* Please provide detailed instructions on how to run your test project. We will test this carefully and use your submission as a base for questions in the next conversation.

## Project structure

```
zuratech-tha/
├── docker-compose.yml # Orchestrates WordPress, CodeIgniter API, Redis, MySQL services
├── db/ # Initial database dumps
├── wordpress/
│   ├── plugins/ # Mount point for custom WordPress plugins, please your custom plugin here.
│   └── ...
├── static-site/
│   ├── public/ # Nginx-served build output (Gatsby)
│   └── ...
├── codeigniter/ # CodeIgniter 3 REST API (users & products) with migrations, seeders, and CLI tools
│   ├── application/
│   │   ├── controllers/ # REST controllers (Status, Users, Products) and CLI utilities
│   │   ├── models/ # Domain models (User_model, Product_model)
│   │   ├── migrations/ # Database schema migrations (users, products)
│   └── ...
├── .env
└── README.md
```

## Installation

Make sure you have docker installed on your local machine

Then build and start containers
```bash
docker compose up --build -d
```
To Stop containers:
```
docker compose down
```

Run API service migrations and seeds
NOTE: Make sure to do that inside the container

```bash
docker compose exec codeigniter php app/index.php cli/migrate/latest
docker compose exec codeigniter php app/index.php cli/seeder/run
```

## Running Project
While you docker services are running you will be able to access:
* Static Site: http://localhost:8000/
* API: http://localhost:8020/index.php/ user Bearer token from .env for reuquest
Please use Postman collection: `ZuraTHA.postman_collection.json` for easier testing of API endpoints
* Wordpress admin: http://localhost:8010/wp-admin, credentials: `admin`|`1hFESR&#H7@vYohOEJ`
* Wordpress PHPMYadmin: http://localhost:8081/
* API PHPMYadmin: http://localhost:8082/


## Task 1: WordPress Plugin & API Endpoint (Backend Focus)
This task assesses your PHP and WordPress development skills, which are crucial for managing our clients’ sites.

**Your Goal:** Create a small WordPress plugin that registers a custom post type called UserProducts and exposes its data via a custom REST API endpoint.

**Requirements:**
1. Create a basic WordPress plugin that can be installed and activated.
2. Inside the plugin, create integration with codeigniter API service.`. The plugin must provide an enhanced and cached data from the API to be served as endpoints for Gatsby builder.
3. Users, Products must be pulled from codeigniter API service.
4. In the plugin admin page, please create a box to enter and save User.id, which data will be pulled from the API.
4. Create a custom REST API endpoints in Wordpress: `GET /wp-json/my-api/v1/user/:id/products`
5. Endpoint should return a JSON array of all published user products.

**What to provide:** The complete plugin folder containing your PHP code.

## Task 2: Gatsby Frontend Page (Frontend Focus)
This task assesses your frontend skills, particularly with headless architecture, React/Gatsby, and responsive styling.

**Your Goal:** Build a single, responsive page in a Gatsby application that fetches and displays the User's Products from the WordPress API you created in Task 1.

**Requirements:**
1. Use the provided Gatsby starter project.
2. Fetch data from your /wp-json/my-api/v1/user/:id/product endpoint when the page loads.
3. Display the projects in a responsive grid. Please use the provided design mockup for general layout and styling cues.
3.1 On desktop screens, display projects in a 3-column grid.
3.2 On mobile screens, display them in a single-column list.
4. Each product in the grid should display its name, imag, sku and description.
5. Use SASS/SCSS for all styling. We want to see how you organize your styles (e.g., variables, mixins, file structure).

**What to provide:** The complete runnable Gatsby project folder. Instruction how to run this locally.

## Bonus Task: Refactor Legacy Code (Architectural Focus)
This optional task assesses your ability to analyze and improve existing code, a key part of supporting our older applications.
**Your Goal:** Identify key issues in a provided CodeIgniter 3 application and provide a list of improvements for scalability and automation.

