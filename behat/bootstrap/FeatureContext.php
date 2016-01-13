<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  protected $sphUsers;

  /**
   * Initializes context.
   *
   * Every scenario gets its own context object.
   *
   * @param array $users .
   *   Context parameters (set them up through behat.yml or behat.local.yml).
   */
  public function __construct(array $users) {
    if (isset($users)) {
      $this->sphUsers = $users;
    }
    if (!isset($users['subscriber'])) {
      throw new Exception("You need to add a subscriber user. Please check behat.yml.");
    }
    if (!isset($users['registered non-subscriber'])) {
      throw new Exception("You need to add a subscriber user. Please check behat.yml.");
    }
  }

  /**
   * Private function for the whoami step.
   */
  private function whoami() {
    $element = $this->getSession()->getPage();
    $this->getSession()->visit($this->locatePath('/global'));
    if ($find = $element->find('css', '.navbar-user .nav-user')) {
      $username = $find->getText();
      var_dump($username);
      if ($username) {
        return $username;
      }
    }

    return FALSE;
  }

  /**
   * Helper function to fetch usernames and passwords stored in behat.local.yml.
   *
   * @param string $type
   *   The type of user to fetch the username and password for.
   *
   * @return array
   *   The username and matching password or FALSE on error.
   *
   * @throws \Exception
   */
  public function fetchCredentials($type) {
    try {
      return array(
        'username' => key($this->sphUsers[$type]),
        'password' => current($this->sphUsers[$type]),
      );
    } catch (Exception $e) {
      throw new Exception("Non-existant user/password for $type please check behat.yml.");
    }
  }

  /**
   * Authenticates a user.
   *
   * @Given /^I am logged in as "([^"]*)" with the password "([^"]*)"$/
   *
   * @param $username
   * @param $passwd
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Exception
   */
  public function iAmLoggedInAsWithThePassword($username, $password) {
    $user = $this->whoami();

    // Already logged in as the correct user.
    if (strtolower($user) == strtolower($username)) {
      return;
    }
    // Already logged in, but as the wrong user, so log out first.
    else if ($user !== FALSE) {
      $page = $this->getSession()->getPage();
      $logout_link = $page->findLink($this->getDrupalText('log_out'));
      $logout_link->click();
    }

    $element = $this->getSession()->getPage();
    if (empty($element)) {
      throw new Exception('Page not found');
    }

    $page = $this->getSession()->getPage();
    $login_link = $page->findLink($this->getDrupalText('log_in'));
    $login_link->click();

    $page = $this->getSession()->getPage();
    $page->fillField($this->getDrupalText('username_field'), $username);
    $page->fillField($this->getDrupalText('password_field'), $password);
    $submit = $page->findButton("Log in");
    if (!$submit) {
      throw new Exception('No submit button on "' . $this->getSession()->getCurrentUrl() . '".');
    }
    $submit->click();
    $this->getSession()->wait(5000);

    $user = $this->whoami();
    if (strtolower($user) != strtolower($username)) {
      throw new Exception("Failed to login.");
    }
  }

  /**
   * Authenticates a user with password from configuration.
   *
   * @Given /^I am logged in as a "([^"]*)"$/
   *
   * @param $user_type
   * @throws \Exception
   */
  public function iAmLoggedInAs($user_type) {
    $credentials = $this->fetchCredentials($user_type);
    $this->iAmLoggedInAsWithThePassword($credentials['username'], $credentials['password']);
  }

  /**
   * Load the homepage
   *
   * @Given /^I am on the global homepage$/
   */
  public function iAmOnGlobalHomePage() {
    $this->visitPath("/global");
  }

  /**
   * @Then /^I should not see the paywall popup$/
   */
  public function iShouldNotSeeThePaywallPopup() {
    $element = $this->getSession()->getPage();
    $node = $element->find('css', '.paywall-fixed-popup');
    if ($node) {
      if ($node->isVisible()) {
        throw new \Exception("Paywall popup is visble");
      }
      else {
        return;
      }
    }
    throw new ElementNotFoundException($this->getSession(), 'paywall popup', '.paywall-fixed-popup');
  }

  /**
   * @Then /^I should see the paywall popup$/
   */
  public function iShouldSeeThePaywallPopup() {
    $element = $this->getSession()->getPage();
    $node = $element->find('css', '.paywall-fixed-popup');
    if ($node) {
      if ($node->isVisible()) {
        return;
      }
      else {
        throw new \Exception("Paywall popul is not visble");
      }
    }
    throw new ElementNotFoundException($this->getSession(), 'paywall popup', '.paywall-fixed-popup');
  }

  /**
   * @AfterStep
   */
  public function takeScreenShotAfterFailedStep(afterStepScope $scope) {
    if (99 === $scope->getTestResult()->getResultCode()) {
      $driver = $this->getSession()->getDriver();
      if (!($driver instanceof Selenium2Driver)) {
        return;
      }
      $this->getSession()->resizeWindow(1440, 900, 'current');
      file_put_contents('./screenshot-fail.png', $this->getSession()->getDriver()->getScreenshot());
    }
  }
}
