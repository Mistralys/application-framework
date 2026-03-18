# AI Module - File Structure
_SOURCE: AI Module File Structure_
# AI Module File Structure
###  
```
└── src/
    └── classes/
        └── Application/
            └── AI/
                └── AIToolException.php
                └── BaseAIToolContainer.php
                └── Cache/
                    ├── AICacheLocation.php
                    ├── AICacheStrategyInterface.php
                    ├── BaseAICacheStrategy.php
                    ├── Events/
                    │   ├── RegisterAIIndexCacheListener.php
                    ├── Strategies/
                    │   └── FixedDurationStrategy.php
                    │   └── UncachedStrategy.php
                └── EnvironmentRunner.php
                └── Server/
                    ├── FrameworkMCPServer.php
                    ├── StderrLogger.php
                └── Tools/
                    └── AIToolInterface.php
                    └── BaseAITool.php

```