@issue-III-165
Scenario: remove repetition of short description in long description for events ONLY when FULL short description is equal to the first part of long description.
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
  Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
  adipiscing elit: Donec non velit eu eros eleifend mattis. Mauris
  tristique scelerisque consectetur. Morbi a congue purus, quis
  tempor arcu. Nam bibendum risus vel nulla feugiat finibus. Aenean
  vestibulum nisi vel nisl elementum, quis faucibus ex dictum
  nullam.\n\n
  Lange Beschrijving - Donec porta molestie arcu, ut tempor odio. Cras mauris nisl,
  rhoncus et tortor id, lobortis ornare libero. Vivamus tellus eros,
  semper sit amet gravida ac, pellentesque a tortor. Etiam
  sollicitudin mauris vitae purus pellentesque, sit amet elementum
  lacus suscipit. Duis id felis sed justo placerat facilisis
  convallis id purus. Praesent fermentum, odio vel varius
  scelerisque, arcu tortor sagittis nisi, et egestas turpis sem vitae
  orci. Maecenas hendrerit nulla ultrices nulla porttitor, nec
  tincidunt odio cursus.\n\n
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
  Ut massa purus, luctus non ex tempor, suscipit efficitur mi.
  """
  # beschrijving niet genest binnen p-tags
  # <br> wordt omgezet naar \n
  # geen \n na elke 60-tal karakters (wordt nu automatisch toegevoegd)
  #
  # korte en lange beschrijving worden samengevoegd;
  # indien de volledige korte beschrijving identiek is aan de eerste x-aantal karakters van de lange beschrijving,
  # waarbij het x-aantal karakters gelijk is aan het aantal karakters van de korte beschrijving,
  # worden identieke values van lange beschrijving weggelaten.
  #
  # Bron-vermelding wordt gestript uit description


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
  Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
  adipiscing elit. Donec non velit eu eros eleifend mattis. Mauris
  tristique scelerisque consectetur. Morbi a congue purus, quis
  tempor arcu. Nam bibendum risus vel nulla feugiat finibus. Aenean
  vestibulum nisi vel nisl elementum, quis faucibus ex dictum
  nullam.\n\n
  Lange Beschrijving - Donec porta molestie arcu, ut tempor odio. Cras mauris nisl,
  rhoncus et tortor id, lobortis ornare libero. Vivamus tellus eros,
  semper sit amet gravida ac, pellentesque a tortor. Etiam
  sollicitudin mauris vitae purus pellentesque, sit amet elementum
  lacus suscipit. Duis id felis sed justo placerat facilisis
  convallis id purus. Praesent fermentum, odio vel varius
  scelerisque, arcu tortor sagittis nisi, et egestas turpis sem vitae
  orci. Maecenas hendrerit nulla ultrices nulla porttitor, nec
  tincidunt odio cursus.\n\n
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
  Ut massa purus, luctus non ex tempor, suscipit efficitur mi.
  """
  # beschrijving niet genest binnen p-tags
  # <br> worden omgezet in \n
  # geen \n na elke 60-tal karakters (wordt nu automatisch toegevoegd)
  #
  # korte en lange beschrijving worden samengevoegd;
  # indien de volledige korte beschrijving identiek is aan de eerste x-aantal karakters van de lange beschrijving,
  # waarbij het x-aantal karakters gelijk is aan het aantal karakters van de korte beschrijving,
  # worden identieke values van lange beschrijving weggelaten.
  #
  # Bronvermelding wordt gestript uit beschrijving

@issue-III-165
Scenario: merge short description and long description when short description is partly equal to long description
Given an event in UDB2
And this event has the following short description:
  """
  Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
  adipiscing elit. Donec non velit eu eros eleifend mattis. En deze tekst
  verschilt duidelijk van de lange beschrijving dus dit mag niet
  gestript worden!
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
  Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
  adipiscing elit. Donec non velit eu eros eleifend mattis. En deze tekst
  verschilt duidelijk van de lange beschrijving dus dit mag niet
  gestript worden!\n\n
  Korte beschrijving - Lorem ipsum dolor sit amet, consectetur
  adipiscing elit. Donec non velit eu eros eleifend mattis. Mauris
  tristique scelerisque consectetur. Morbi a congue purus, quis
  tempor arcu. Nam bibendum risus vel nulla feugiat finibus. Aenean
  vestibulum nisi vel nisl elementum, quis faucibus ex dictum
  nullam.\n\n
  Lange Beschrijving - Donec porta molestie arcu, ut tempor odio. Cras mauris nisl,
  rhoncus et tortor id, lobortis ornare libero. Vivamus tellus eros,
  semper sit amet gravida ac, pellentesque a tortor. Etiam
  sollicitudin mauris vitae purus pellentesque, sit amet elementum
  lacus suscipit. Duis id felis sed justo placerat facilisis
  convallis id purus. Praesent fermentum, odio vel varius
  scelerisque, arcu tortor sagittis nisi, et egestas turpis sem vitae
  orci. Maecenas hendrerit nulla ultrices nulla porttitor, nec
  tincidunt odio cursus.\n\n
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
  Ut massa purus, luctus non ex tempor, suscipit efficitur mi.
  """
  # beschrijving niet genest binnen p-tags
  # <br> worden omgezet in \n
  # geen \n na elke 60-tal karakters (wordt nu automatisch toegevoegd)
  # Bronvermelding wordt gestript uit beschrijving

@issue-III-165
Scenario: use long description when there is no short description in UDB2
  Given an event in UDB2
  And this event has no short description
  And this event has the following long description:
  """
  Lange Beschrijving -  Vivamus neque nulla, tristique at porta non,
  sodales non orci. Aenean vehicula aliquam ipsum, ut faucibus lacus
  congue sed. In sagittis egestas turpis. Nam in mi arcu. Sed sit amet
  mollis sem. Suspendisse rhoncus justo libero, a rhoncus sem mattis a.
  Sed felis lectus, semper sed porta quis, venenatis eu tellus. Quisque a
  sapien commodo, scelerisque mauris quis, consectetur erat. Fusce bibendum
  neque id pellentesque porttitor.<br><br>
  Praesent tristique neque quis porttitor porttitor. Cum sociis natoque
  penatibus et magnis dis parturient montes, nascetur ridiculus mus.
  Proin eget purus libero. Fusce enim ipsum, elementum vel commodo quis,
  eleifend ultrices dui. Nunc pretium lectus eros. Donec ut varius dolor,
  a facilisis purus. Donec sit amet bibendum diam, in varius libero.
  Aenean ultricies nisi non velit rutrum pellentesque sit amet at tellus.
  Aenean placerat elementum purus eu mollis. Curabitur eget condimentum lacus.<br>
  <p class="uiv-source">Bron: <a href="http://www.uitinvlaanderen.be/agenda/e/een-piano-in-de-tu-n-joodse-rituelen-en-gebruiken/3aee552e-2071-46a1-beff-d73b31718ea6">UiTinVlaanderen.be</a></p>
  """
  When this event is imported in UDB3
  Then the description of this event in UDB3 equals
  """
  Lange Beschrijving -  Vivamus neque nulla, tristique at porta non,
  sodales non orci. Aenean vehicula aliquam ipsum, ut faucibus lacus
  congue sed. In sagittis egestas turpis. Nam in mi arcu. Sed sit amet
  mollis sem. Suspendisse rhoncus justo libero, a rhoncus sem mattis a.
  Sed felis lectus, semper sed porta quis, venenatis eu tellus. Quisque a
  sapien commodo, scelerisque mauris quis, consectetur erat. Fusce bibendum
  neque id pellentesque porttitor.\n\n
  Praesent tristique neque quis porttitor porttitor. Cum sociis natoque
  penatibus et magnis dis parturient montes, nascetur ridiculus mus.
  Proin eget purus libero. Fusce enim ipsum, elementum vel commodo quis,
  eleifend ultrices dui. Nunc pretium lectus eros. Donec ut varius dolor,
  a facilisis purus. Donec sit amet bibendum diam, in varius libero.
  Aenean ultricies nisi non velit rutrum pellentesque sit amet at tellus.
  Aenean placerat elementum purus eu mollis. Curabitur eget condimentum lacus.
  """
  # beschrijving niet genest binnen p-tags
  # <br> wordt omgezet naar \n
  # geen \n na elke 60-tal karakters (wordt nu automatisch toegevoegd)
  # Bronvermelding wordt gestript uit beschrijving

@issue-III-165
Scenario: merge short description and long description when short description is partly equal to long description and keep HTML of long description
  Given an event in UDB2
  And this event has the following short description:
  """
  Vleermuizen zijn top!
  """
  And this event has the following long description:
  """
  &lt;p style="text-align: center;"&gt;&lt;a class="btn" href="#Inschrijven" target="_self"&gt;Schrijf je hier in!&lt;/a&gt;&lt;br&gt;&lt;br&gt;Al sinds jaar en dag worden vleermuizen geassocieerd met duistere en duivelse machten.&amp;nbsp; De geheimzinnige, nachtelijke levenswijze van de vleermuizen zal hier zeker voor iets tussen zitten.&amp;nbsp; Bij het invallen van de duisternis verschijnen ze, en fladderen ze vervaarlijk dicht om je heen.&amp;nbsp;&lt;/p&gt;&lt;p&gt;Tijdens deze basiscursus bestuderen we de vleermuizen van naderbij en leren zo hun boeiende wereld kennen.&amp;nbsp; We bespreken de algemene kenmerken, de soorten en de waarnemingen van de inlandse vleermuizen. Ook het leren werken met de batdetector komt aan bod.&lt;/p&gt;&lt;p&gt;Kortom, de ideale cursus voor wie zijn eerste passen wil zetten in de fascinerende wereld van de vleermuizen.&lt;/p&gt;&lt;p style="margin-bottom: 0cm; line-height: 150%;"&gt;&lt;strong&gt;Voor deze activiteit kan je online inschrijven (zie deze pagina onderaan). Na de digitale confirmatie kan het gepaste bedrag overgeschreven worden op rekening BE37 0015 0901 2428 t.n.v. Natuurpunt Averbode Bos &amp;amp; Heide met vermelding van 'cursus vleermuizen' en desgevallend je lidnummer.&lt;br&gt;&lt;a name="Inschrijven"&gt;&lt;/a&gt;&amp;nbsp;&lt;/strong&gt;&lt;/p&gt;
  """
  When this event is imported in UDB3
  Then the description of this event in UDB3 equals:
  """
  Vleermuizen zijn top!\n\n<p style=\"text-align: center;\"><a class=\"btn\" href=\"#Inschrijven\" target=\"_self\">Schrijf je hier in!<\/a><br><br>\nAl sinds jaar en dag worden vleermuizen geassocieerd met duistere en duivelse machten.  De geheimzinnige, nachtelijke levenswijze van de vleermuizen zal hier zeker voor iets tussen zitten.  Bij het invallen van de duisternis verschijnen ze, en fladderen ze vervaarlijk dicht om je heen. <\/p>\n<p>Tijdens deze basiscursus bestuderen we de vleermuizen van naderbij en leren zo hun boeiende wereld kennen.  We bespreken de algemene kenmerken, de soorten en de waarnemingen van de inlandse vleermuizen. Ook het leren werken met de batdetector komt aan bod.<\/p>\n<p>Kortom, de ideale cursus voor wie zijn eerste passen wil zetten in de fascinerende wereld van de vleermuizen.<\/p>\n<p style=\"margin-bottom: 0cm; line-height: 150%;\"><strong>Voor deze activiteit kan je online inschrijven (zie deze pagina onderaan). Na de digitale confirmatie kan het gepaste bedrag overgeschreven worden op rekening BE37 0015 0901 2428 t.n.v. Natuurpunt Averbode\nBos &amp; Heide met vermelding van 'cursus vleermuizen' en desgevallend je lidnummer.<br><a name=\"Inschrijven\" id=\"Inschrijven\"><\/a> <\/strong><\/p>
  """
  # geen \n na elke 60-tal karakters (wordt nu automatisch toegevoegd: III-1638)
  # korte en lange beschrijving worden samengevoegd; > het aantal karakters van korte beschrijving wordt vergeleken met zelfde aantal karakters van lange beschrijving (stripped van html-opmaak): indien beide identiek zijn wordt de korte beschrijving niet samengevoegd aan lange beschrijving
  # html longdescription wordt behouden

@issue-III-165
Scenario: remove repetition of short description in long description for events ONLY when FULL short description is equal to the first part of long description and keep HTML of long description
  Given an event in UDB2
  And this event has the following short description:
  """
  Vertelavond met Jan Gabriëls, voorzitter van de Likona vogelwerkgroep: Belevenissen van vroeger en nu ...Wil je meeluisteren, geef een seintje aan natuurpunt.genk@hotmail.com en we reserveren alvast een plaatsje en drankje voor u.Bijdrage aan deze avond: leden Natuurpunt 3 € – niet leden 5 €
  """
  And this event has the following long description:
  """
  &lt;p&gt;Vertelavond met Jan Gabriëls, voorzitter van de Likona vogelwerkgroep: Belevenissen van vroeger en nu ...&lt;/p&gt;&lt;p&gt;Wil je meeluisteren, geef een seintje aan &lt;a href="mailto:natuurpunt.genk@hotmail.com" target="_self"&gt;natuurpunt.genk@hotmail.com&lt;/a&gt; en we reserveren alvast een plaatsje en drankje voor u.&lt;/p&gt;&lt;p&gt;Bijdrage aan deze avond: leden Natuurpunt 3 € – niet leden 5 €&lt;/p&gt;
  """
  When this event is imported in UDB3
  Then the description of this event in UDB3 equals
  """
  <p>Vertelavond met Jan Gabriëls, voorzitter van de Likona vogelwerkgroep: Belevenissen van vroeger en nu ...<\/p> <p>Wil je meeluisteren, geef een seintje aan <a href=\"mailto:natuurpunt.genk@hotmail.com\" target=\"_self\">natuurpunt.genk@hotmail.com<\/a> en we reserveren alvast een plaatsje en drankje voor u.<\/p>\n<p>Bijdrage aan deze avond: leden Natuurpunt 3 € – niet leden 5 €<\/p>
  """
  # geen \n na elke 60-tal karakters (wordt nu automatisch toegevoegd: III-1638)
  # korte en lange beschrijving worden samengevoegd; > het aantal karakters van korte beschrijving wordt vergeleken met zelfde aantal karakters van lange beschrijving (stripped van html-opmaak): indien beide identiek zijn wordt de korte beschrijving niet samengevoegd aan lange beschrijving
  # html longdescription wordt behouden