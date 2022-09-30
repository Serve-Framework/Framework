# Serve Framework Tests

[![Build Status](https://github.com/Serve-Framework/Framework/workflows/Tests/badge.svg)](https://github.com/Serve-Framework/Framework/actions?query=workflow%3ATests)

Here you'll find all the Serve framework tests. They are divided in to groups so you can easily run the tests you want.

	php vendor/bin/phpunit  --group unit

	php vendor/bin/phpunit  --exclude-group integration

| Group                | Description                                                           |
|----------------------|-----------------------------------------------------------------------|
| unit                 | All unit tests                                                        |
| integration          | All integration tests                                                 |
| integration:database | All integration tests that touch the database                         |
| slow                 | All slow tests (both unit and integration)                            |