# Matos

## Description
Matos is a primitive equipment management app to track loans to students.

## Installation
1. Clone the repository.
2. Install the required dependencies by running the following commands:
   - `composer install`
   - `npm install`
3. Setup your desired database management system (postgresql or Mysql recommended) and add the credentials to a newly created `.env.local` file (next to the existing `.env` file in the project root).

## Development
To start the development environment, run the following commands:
- `php bin/console d:m:m` - Create the database schema.
- `php bin/console d:f:l` - Apply the database fixtures (test data).
- `npm run watch` - Start the watch mode for your frontend assets.
- `symfony server:start` - Start the Symfony development server.

## Usage
1. Run the application using the development commands mentioned above.
2. Access the application in your browser at `http://localhost:8000` (or the specified port).

## Contributing
If you would like to contribute to this project, please follow these steps:
1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Make your changes and commit them.
4. Push your changes to your forked repository.
5. Submit a pull request. 
