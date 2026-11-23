@message @message_ntfy
Feature: Users choose the ntfy topic their notifications go to
  In order to get Moodle notifications on my phone
  As a user
  I need to enter my ntfy topic in my notification preferences

  Scenario: An administrator points the plugin at their own server
    Given I log in as "admin"
    When I visit "/admin/settings.php?section=messagesettingntfy"
    And I set the following fields to these values:
      | ntfy server  | https://ntfy.school.example |
      | Access token | tk_example                  |
    And I press "Save changes"
    Then the field "ntfy server" matches value "https://ntfy.school.example"

  @javascript
  Scenario: A user saves a topic and sees it again
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
    And I log in as "admin"
    And I navigate to "Messaging > Notification settings" in site administration
    And I set the field "ntfy" to "1"
    And I press "Save changes"
    And I set the following fields to these values:
      | mod_assign_assign_notification_enabled[ntfy] | 1 |
      | mod_assign_assign_notification_locked[ntfy]  | 0 |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I follow "Preferences" in the user menu
    And I click on "Notification preferences" "link" in the "#page-content" "css_element"
    When I click on "[data-processor-name='ntfy'] [data-processor-setting]" "css_element"
    And I set the field "Your ntfy topic" to "my-phone_42"
    And I click on "Save changes" "button" in the ".modal-dialog" "css_element"
    And I reload the page
    And I click on "[data-processor-name='ntfy'] [data-processor-setting]" "css_element"
    Then the field "Your ntfy topic" matches value "my-phone_42"
