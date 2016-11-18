@issue-III-165
Scenario: remove repetition of short description in long description for events.
Given an event in UDB2
And this event has the following short description:
    """
    Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
    adipiscing elit: Donec non velit eu eros eleifend mattis. Mauris
    tristique scelerisque consectetur. Morbi a congue purus, quis
    tempor arcu. Nam bibendum risus vel nulla feugiat finibus. Aenean
    vestibulum nisi vel nisl elementum, quis faucibus ex dictum
    nullam.
    """
And this event has the following long description:
    """
    Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
    adipiscing elit: Donec non velit eu eros eleifend mattis. Mauris
    tristique scelerisque consectetur. Morbi a congue purus, quis
    tempor arcu. Nam bibendum risus vel nulla feugiat finibus. Aenean
    vestibulum nisi vel nisl elementum, quis faucibus ex dictum
    nullam.<br><br>
    Lange Beschrijving - Donec porta molestie arcu, ut tempor odio. Cras mauris nisl,
    rhoncus et tortor id, lobortis ornare libero. Vivamus tellus eros,
    semper sit amet gravida ac, pellentesque a tortor. Etiam
    sollicitudin mauris vitae purus pellentesque, sit amet elementum
    lacus suscipit. Duis id felis sed justo placerat facilisis
    convallis id purus. Praesent fermentum, odio vel varius
    scelerisque, arcu tortor sagittis nisi, et egestas turpis sem vitae
    orci. Maecenas hendrerit nulla ultrices nulla porttitor, nec
    tincidunt odio cursus.<br><br>
    Curabitur et diam nisi. In hac habitasse platea dictumst. Aliquam
    suscipit, arcu nec lobortis ultricies, arcu nibh varius tortor, sit
    amet rhoncus mauris ante id augue. Nullam est justo, sodales vitae
    nunc eget, mattis laoreet arcu. Curabitur pharetra hendrerit
    turpis, in placerat elit accumsan eu. Curabitur vulputate lacus in
    leo scelerisque condimentum. Sed sodales justo sit amet porta
    scelerisque. Sed metus urna, tempor ut pellentesque ut, fermentum
    in ligula. Pellentesque ullamcorper, nisi non tempus congue, massa
    ligula vestibulum nulla, sit amet cursus ligula felis et purus.
    Suspendisse eleifend ante nibh, eget aliquam neque suscipit ac. In
    hac habitasse platea dictumst. Praesent tempus faucibus enim nec
    hendrerit. Nullam non purus vel lacus dignissim cursus eu ac est.
    Nulla tellus mauris, maximus sed faucibus non, egestas quis eros.
    Ut massa purus, luctus non ex tempor, suscipit efficitur mi.<br>
    <p class="uiv-source">Bron: <a href="http://www.uitinvlaanderen.be/agenda/e/een-piano-in-de-tu-n-joodse-rituelen-en-gebruiken/3aee552e-2071-46a1-beff-d73b31718ea6">UiTinVlaanderen.be</a></p>

    """
When this event is imported in UDB3
Then the description of this event in UDB3 equals:
    """
    <p>Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
    adipiscing elit: Donec non velit eu eros eleifend mattis. Mauris
    tristique scelerisque consectetur. Morbi a congue purus, quis
    tempor arcu. Nam bibendum risus vel nulla feugiat finibus. Aenean
    vestibulum nisi vel nisl elementum, quis faucibus ex dictum
    nullam.<br><br>
    Lange Beschrijving - Donec porta molestie arcu, ut tempor odio. Cras mauris nisl,
    rhoncus et tortor id, lobortis ornare libero. Vivamus tellus eros,
    semper sit amet gravida ac, pellentesque a tortor. Etiam
    sollicitudin mauris vitae purus pellentesque, sit amet elementum
    lacus suscipit. Duis id felis sed justo placerat facilisis
    convallis id purus. Praesent fermentum, odio vel varius
    scelerisque, arcu tortor sagittis nisi, et egestas turpis sem vitae
    orci. Maecenas hendrerit nulla ultrices nulla porttitor, nec
    tincidunt odio cursus.<br><br>
    Curabitur et diam nisi. In hac habitasse platea dictumst. Aliquam
    suscipit, arcu nec lobortis ultricies, arcu nibh varius tortor, sit
    amet rhoncus mauris ante id augue. Nullam est justo, sodales vitae
    nunc eget, mattis laoreet arcu. Curabitur pharetra hendrerit
    turpis, in placerat elit accumsan eu. Curabitur vulputate lacus in
    leo scelerisque condimentum. Sed sodales justo sit amet porta
    scelerisque. Sed metus urna, tempor ut pellentesque ut, fermentum
    in ligula. Pellentesque ullamcorper, nisi non tempus congue, massa
    ligula vestibulum nulla, sit amet cursus ligula felis et purus.
    Suspendisse eleifend ante nibh, eget aliquam neque suscipit ac. In
    hac habitasse platea dictumst. Praesent tempus faucibus enim nec
    hendrerit. Nullam non purus vel lacus dignissim cursus eu ac est.
    Nulla tellus mauris, maximus sed faucibus non, egestas quis eros.
    Ut massa purus, luctus non ex tempor, suscipit efficitur mi.</p>
    """

@issue-III-165
Scenario: merge short description and long description when short description is not repeated in long description for events.
Given an event in UDB2
And this event has the following short description:
    """
    Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
    adipiscing elit. Donec non velit eu eros eleifend mattis. Mauris
    tristique scelerisque consectetur. Morbi a congue purus, quis
    tempor arcu. Nam bibendum risus vel nulla feugiat finibus. Aenean
    vestibulum nisi vel nisl elementum, quis faucibus ex dictum
    nullam.
    """
And this event has the following long description:
    """
    Lange Beschrijving - Donec porta molestie arcu, ut tempor odio. Cras mauris nisl,
    rhoncus et tortor id, lobortis ornare libero. Vivamus tellus eros,
    semper sit amet gravida ac, pellentesque a tortor. Etiam
    sollicitudin mauris vitae purus pellentesque, sit amet elementum
    lacus suscipit. Duis id felis sed justo placerat facilisis
    convallis id purus. Praesent fermentum, odio vel varius
    scelerisque, arcu tortor sagittis nisi, et egestas turpis sem vitae
    orci. Maecenas hendrerit nulla ultrices nulla porttitor, nec
    tincidunt odio cursus.<br><br>
    Curabitur et diam nisi. In hac habitasse platea dictumst. Aliquam
    suscipit, arcu nec lobortis ultricies, arcu nibh varius tortor, sit
    amet rhoncus mauris ante id augue. Nullam est justo, sodales vitae
    nunc eget, mattis laoreet arcu. Curabitur pharetra hendrerit
    turpis, in placerat elit accumsan eu. Curabitur vulputate lacus in
    leo scelerisque condimentum. Sed sodales justo sit amet porta
    scelerisque. Sed metus urna, tempor ut pellentesque ut, fermentum
    in ligula. Pellentesque ullamcorper, nisi non tempus congue, massa
    ligula vestibulum nulla, sit amet cursus ligula felis et purus.
    Suspendisse eleifend ante nibh, eget aliquam neque suscipit ac. In
    hac habitasse platea dictumst. Praesent tempus faucibus enim nec
    hendrerit. Nullam non purus vel lacus dignissim cursus eu ac est.
    Nulla tellus mauris, maximus sed faucibus non, egestas quis eros.
    Ut massa purus, luctus non ex tempor, suscipit efficitur mi.<br>
    <p class="uiv-source">Bron: <a href="http://www.uitinvlaanderen.be/agenda/e/een-piano-in-de-tu-n-joodse-rituelen-en-gebruiken/3aee552e-2071-46a1-beff-d73b31718ea6">UiTinVlaanderen.be</a></p>
    """
When this event is imported in UDB3
Then the description of this event in UDB3 equals:
    """
    <p>Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
    adipiscing elit. Donec non velit eu eros eleifend mattis. Mauris
    tristique scelerisque consectetur. Morbi a congue purus, quis
    tempor arcu. Nam bibendum risus vel nulla feugiat finibus. Aenean
    vestibulum nisi vel nisl elementum, quis faucibus ex dictum
    nullam.<br>
    Lange Beschrijving - Donec porta molestie arcu, ut tempor odio. Cras mauris nisl,
    rhoncus et tortor id, lobortis ornare libero. Vivamus tellus eros,
    semper sit amet gravida ac, pellentesque a tortor. Etiam
    sollicitudin mauris vitae purus pellentesque, sit amet elementum
    lacus suscipit. Duis id felis sed justo placerat facilisis
    convallis id purus. Praesent fermentum, odio vel varius
    scelerisque, arcu tortor sagittis nisi, et egestas turpis sem vitae
    orci. Maecenas hendrerit nulla ultrices nulla porttitor, nec
    tincidunt odio cursus.<br><br>
    Curabitur et diam nisi. In hac habitasse platea dictumst. Aliquam
    suscipit, arcu nec lobortis ultricies, arcu nibh varius tortor, sit
    amet rhoncus mauris ante id augue. Nullam est justo, sodales vitae
    nunc eget, mattis laoreet arcu. Curabitur pharetra hendrerit
    turpis, in placerat elit accumsan eu. Curabitur vulputate lacus in
    leo scelerisque condimentum. Sed sodales justo sit amet porta
    scelerisque. Sed metus urna, tempor ut pellentesque ut, fermentum
    in ligula. Pellentesque ullamcorper, nisi non tempus congue, massa
    ligula vestibulum nulla, sit amet cursus ligula felis et purus.
    Suspendisse eleifend ante nibh, eget aliquam neque suscipit ac. In
    hac habitasse platea dictumst. Praesent tempus faucibus enim nec
    hendrerit. Nullam non purus vel lacus dignissim cursus eu ac est.
    Nulla tellus mauris, maximus sed faucibus non, egestas quis eros.
    Ut massa purus, luctus non ex tempor, suscipit efficitur mi.</p>
    """