Feature: Home Page

  Ensure the home page content is available

  @javascript @anon
  Scenario: View the homepage content anonymously
    Given I am on the global homepage
    Then I should see "Top Stories"

  @javascript @anon
  Scenario: View the homepage content anonymously
    Given I am on the global homepage
    Then I should not see the paywall popup

  @javascript @subscriber
  Scenario: View the home page content as a subscriber
    Given I am logged in as a "subscriber"
      And I am on the global homepage
    Then I should see "Top Stories"
      And I should not see the paywall popup
