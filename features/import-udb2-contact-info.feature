Feature: import of contactInfo from UDB2 to UDB3
  @issue-III-1636
  Scenario: parse UDB2 contact info format to UDB3 contact and booking info format
    Given an event in UDB2
    And this event has the following contact info:
    """
    <contactinfo>
      <phone type="phone">0473233773</phone>
      <phone type="phone" reservation="true">987654321</cdb:phone>
      <url reservation="true">http://www.test.be</url>
      <url>http://google.be</url>
      <mail>bibliotheek@hasselt.be</mail>
      <mail reservation="true">tickets@test.com</mail>
    </contactinfo>
    """
    When this event is imported in UDB3
    Then the contact info of this event in UDB3 equals:
    """
    "contactPoint": {
      "phone": ["0473233773"],
      "email": ["bibliotheek@hasselt.be"],
      "url": ["http://google.be"],
      "type": ""
    }
    """
    And the booking info of this event in UDB3 equals:
    """
    "bookingInfo":{
      "phone":"987654321",
      "email":"tickets@test.com",
      "url":"http://www.test.be",
      "urlLabel":"Reserveer plaatsen",
   }
    """