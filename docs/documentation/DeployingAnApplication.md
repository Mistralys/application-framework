# Deploying an application

## Deployment tasks

To register a deployment, the `deployment-callback.php` dispatcher file can
be run, which will execute all available deployment tasks. By default, the
time and application version are stored in the deployment history, which
can be viewed in the developer screens.

Additional, custom deployment tasks can be added in the application's classes,
in the subfolder:

```
DeploymentTasks
```

Any classes stored here will be executed automatically. Each task class must
extend the base class `BaseDeployTask`.

> Note: Deployment tasks run in script mode, without authentication
enabled. 
