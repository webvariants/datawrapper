Migrating from 1.x to 2.0
-------------------------

* Due to the new namespaced class layout, you have to change your references from e.g.
  ``DatawrapperHooks`` to ``Datawrapper\Hooks``.
* Admin and account pages (which are collected via the ``GET_ADMIN_PAGES`` and ``GET_ACCOUNT_PAGES``)
  do not contain the controllers anymore. You still have to register for the hooks to provide
  meta-information (name, order, ...) for your controller, but then you must add your controller to
  the router manually (see ``www/index.php`` on how you should ideally do it).
* The entity classes now have a proper prefix, ``Datawrapper\ORM\``, so it's not just ``User``
  anymore, but ``Datawrapper\ORM\User``. Apart from changing the obvious references to the classes,
  you also have to pay attention to any custom queries you are creating, because those need to
  contain fully-qualified class names as well.

  Old code:

  ```php
  <?php

  UserQuery::create()
      ->leftJoin('User.Chart')
      ->withColumn('COUNT(Chart.Id)', 'NbCharts')
      ->groupBy('User.Id')
      ->filterByDeleted(false);
   ```

  New code:

  ```php
  <?php

  use Datawrapper\ORM;

  ORM\UserQuery::create()
      ->leftJoin('Datawrapper\ORM\User.Chart')
      ->withColumn('COUNT(Chart.Id)', 'NbCharts')
      ->groupBy('Datawrapper\ORM\User.Id')
      ->filterByDeleted(false);
   ```
