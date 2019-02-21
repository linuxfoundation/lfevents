Feature: Change blogname and blogdescription
  As a maintainer of the site
  I want to be able to change basic settings
  So that I have control over my site

  Background:
    Given I am logged in as an administrator
    Given I am on the dashboard

  Scenario: Saving blogname
    Given I go to the "Settings > General" menu
    When I fill in "blogname" with "Awesome WordHat Test Site"
    And I press "submit"
    And I should see "Settings saved."
    And I go to "/wp/wp-admin/options-general.php?page=pantheon-cache"
    And I press "Clear Cache"
    And I should see "Site cache flushed." in the ".updated" element
    And the cache is cleared
    And I am on the homepage
    Then I should see "Awesome WordHat Test Site" in the ".site-title > a" element

  Scenario: Saving blogdescription
    Given I go to the "Settings > General" menu
    When I fill in "blogdescription" with "GitHub + Composer + CircleCi + Pantheon = Win!"
    And I press "submit"
    And I should see "Settings saved."
    And I go to "/wp/wp-admin/options-general.php?page=pantheon-cache"
    And I press "Clear Cache"
    And I should see "Site cache flushed." in the ".updated" element
    And I am on the homepage
    Then I should see "GitHub + Composer + CircleCi + Pantheon = Win!" in the ".site-description" element
