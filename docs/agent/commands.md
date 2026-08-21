# Console commands

Read before changing the shared Symfony Console command base or scheduled-command lifecycle.

- `Base\Command\Command` extends Symfony Console directly. Its protected `execute()` signature must remain compatible with the installed Symfony version, including the `int` return type required by current releases.
- Keep `symfony/console` as a direct Composer dependency of this package; do not rely on a consuming application to install a class that the base command extends.
- Successful execution returns `0`. The shared wrapper records command state and catches action exceptions according to the existing lifecycle; subclasses implement `executeAction()` rather than overriding `execute()`.
- Validate signature changes with `php -l`, then install this package in the consuming MVC repository and run its focused command or cron entrypoint before deployment.
