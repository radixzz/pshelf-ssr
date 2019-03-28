# Install
Install PHP module dependencies:
~~~~
composer install
~~~~

Install puppeteer dependencies:
~~~~
npm install
~~~~

# Dependencies
php >= 7.2
node 8

## PHP Intl (The Internationalization extension)
Search for the extension package that applies to your PHP version

~~~~
apt search intl
~~~~

Install the package
~~~~
apt install php7.2-intl
~~~~


[Install graphic libraries for puppeteer](https://techoverflow.net/2018/06/05/how-to-fix-puppetteer-error-while-loading-shared-libraries-libx11-xcb-so-1-cannot-open-shared-object-file-no-such-file-or-directory/)

# Solve Issues
## Vagrant cant execute npm install
[yarn install --no-bin-links](https://github.com/laravel/homestead/issues/922)
## Cannot write

# Todo
+ Cleanup unused dependencies from composer
+ Implement work queues to execute the prerenders like RabbitMQ, Celery, etc
+ Create commands to clear cache or dump current status
+ Pack into a composer package
+ Set better logging
+ Refactor URL validation with the Request class
+ Re-test on clean environment
+ Add concurrent tasks with configurable limit