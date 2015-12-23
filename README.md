Example automated tests for http://www.straitstimes.com/ built using:

* behat
* selenium

# How to install

## Run composer

```
composer install
```

## Download selenium

```
wget "http://selenium-release.storage.googleapis.com/2.48/selenium-server-standalone-2.48.2.jar"
```

## Run selenium

```
java -jar selenium-server-standalone-2.48.2.jar &
```

# Run behat

```
bin/behat --config=behat/behat.yml --verbose --strict --stop-on-failure
```

## Extra for experts

Find all contexts you can use

```
bin/behat --config=behat/behat.yml -dl
```

Run again the UAT environment

```
bin/behat --config=behat/behat.yml --verbose --strict --stop-on-failure --profile=uat
```
