Feature: Home Page

  Ensure the home page content is available

  @javascript @anon
  Scenario: View the homepage content anonymously
    Given I am on the global homepage
      And I am not logged in
    Then I should see "Top Stories"

  @javascript @subscriber
  Scenario: View the home page content as a subscriber
    Given I am logged in as a test subscriber
      And I am on the global homepage
    Then I should see "Top Stories"
