Feature: import of contactInfo from UDB2 to UDB3
  @issue-III-1636
  Scenario: parse UDB2 contact info format to UDB3 contact info format
    Given an event in UDB2
    And this event has the following contact info:
    """
    <contactinfo>
      <phone type="phone">0473233773</phone>
      <url>http://www.test.be</url>
      <mail>bibliotheek@hasselt.be</mail>
    </contactinfo>
    """
    When this event is imported in UDB3
    Then the contact info of this event in UDB3 equals:
    """
    "contactPoint": {
      "phone": ["011 24 43 00"],
      "email": ["bibliotheek@hasselt.be"],
      "url": ["http://www.test.be"],
      "type": “”
    }
    """

  // TODO write scenario for seeAlso. See comment https://jira.uitdatabank.be/browse/III-1636