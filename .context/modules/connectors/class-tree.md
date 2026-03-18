# Connectors - Class File Structure
_SOURCE: Tree of PHP Class Files_
# Tree of PHP Class Files
###  
```
└── src/
    └── classes/
        └── Connectors/
            └── Connector/
                ├── BaseConnector.php
                ├── BaseConnectorMethod.php
                ├── ConnectorException.php
                ├── ConnectorInterface.php
                ├── Method/
                │   ├── Delete.php
                │   ├── Get.php
                │   ├── Post.php
                │   ├── Put.php
                ├── Stub/
                │   ├── Method/
                │   │   └── StubFailureMethod.php
                ├── StubConnector.php
            └── Connectors.php
            └── ConnectorsException.php
            └── Headers/
                ├── HTTPHeader.php
                ├── HTTPHeadersBasket.php
            └── ProxyConfiguration.php
            └── README.md
            └── Request.php
            └── Request/
                ├── Cache.php
                ├── Method.php
                ├── RequestSerializer.php
                ├── URL.php
            └── Response.php
            └── Response/
                ├── ResponseEndpointError.php
                ├── ResponseError.php
                ├── ResponseSerializer.php
            └── ResponseCode.php
            └── module-context.yaml

```
---
**File Statistics**
- **Size**: 2.13 KB
- **Lines**: 50
File: `modules/connectors/class-tree.md`
