Feature: import of contactInfo from UDB2 to UDB3
  @issue-III-1636
  Scenario: parse UDB2 contact info format to UDB3 contact info format
    Given an event in UDB2
    And this event has the following contact info:
    """
    <cdb:contactinfo>
      <cdb:phone type="phone">0473233773</phone>
      <cdb:url reservation="true">http://www.test.be</url>
      <cdb:mail>bibliotheek@hasselt.be</mail>
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
    And we can ignore the reservation info from UDB2 because it is parsed to bookingInfo in UDB3