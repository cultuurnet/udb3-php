Feature: UDB2 cdbxml agefrom gets projected to JSON-LD typicalAgeRange property.

  @issue-III-1637
  Scenario Outline: upper boundary of typicalAgeRange for agefrom up to 12 becomes 12
    Given an event in UDB2
    And this event has agefrom <agefrom>
    When this event is imported in UDB3
    Then the typicalAgeRange of this event equals "<agefrom>-12"

    Examples:
    | agefrom |
    | 0       |
    | 3       |
    | 12      |

  @issue-III-1637
  Scenario Outline: upper boundary of typicalAgeRange for agefrom between 13 and 18 becomes 18
    Given an event in UDB2
    And this event has agefrom <agefrom>
    When this event is imported in UDB3
    Then the typicalAgeRange of this event equals "<agefrom>-18"

    Examples:
      | agefrom |
      | 13      |
      | 18      |

  @issue-III-1637
  Scenario Outline: upper boundary of typicalAgeRange for agefrom higher than 18 becomes 99
    Given an event in UDB2
    And this event has agefrom <agefrom>
    When this event is imported in UDB3
    Then the typicalAgeRange of this event equals "<agefrom>-99"

    Examples:
      | agefrom |
      | 19      |
      | 99      |

  @issue-III-1637
  Scenario Outline: lower boundary of typicalAgeRange for agefrom higher than 99 becomes 99
    Given an event in UDB2
    And this event has agefrom <agefrom>
    When this event is imported in UDB3
    Then the typicalAgeRange of this event equals "99-99"

    Examples:
      | agefrom |
      | 100     |
      | 101     |