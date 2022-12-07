@auth @auth_companion
Feature: Switch to a companion account and back
  In order to switch to a companion account and back
  As a teacher
  I need to see the button "Switch to companion"
  As a companion user
  I need to see the button "Switch back"

  Background:
    Given the following config values are set as admin:
      | auth       | companion   |                |
      | namesuffix | (companion) | auth_companion |
      | forcelogin | 0           | auth_companion |
    And the following "permission overrides" exist:
      | capability                    | permission | role             | contextlevel | reference |
      | auth/companion:allowcompanion | Allow      | editingteacher   | System       |           |
    And the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  @javascript
  Scenario: Switch to the companion account
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "#user-menu-toggle" "css_element"
    And I should see "Switch to companion"
    And I click on "Switch to companion" "link"
    And I should see "Companion account"
    And I click on "Switch to companion" "button"
    Then I should see "You are now using your companion account \"Teacher 1 (companion)\"."

  @javascript
  Scenario: Switch back to the main account
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "#user-menu-toggle" "css_element"
    And I should see "Switch to companion"
    And I click on "Switch to companion" "link"
    And I should see "Companion account"
    And I click on "Switch to companion" "button"
    And I should see "You are now using your companion account \"Teacher 1 (companion)\"."

    And I click on "#user-menu-toggle" "css_element"
    And I should see "Switch back"
    And I click on "Switch back" "link"
    And I should see "Switch back to your origin account."
    And I click on "Switch back" "button"
    And I should see "You are now using your origin account \"Teacher 1\"."

  @javascript
  Scenario: Switch back to the main account and force login
    Given the following config values are set as admin:
      | auth       | companion   |                |
      | forcelogin | 1           | auth_companion |

    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "#user-menu-toggle" "css_element"
    And I should see "Switch to companion"
    And I click on "Switch to companion" "link"
    And I should see "Companion account"
    And I click on "Switch to companion" "button"
    And I should see "You are now using your companion account \"Teacher 1 (companion)\"."

    And I click on "#user-menu-toggle" "css_element"
    And I should see "Switch back"
    And I click on "Switch back" "link"
    And I should see "Switch back to your origin account."
    And I click on "Switch back" "button"
    And I should see "Log in to"
