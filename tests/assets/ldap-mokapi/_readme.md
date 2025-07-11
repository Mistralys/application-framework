# LDAP Mock API

This folder contains configuration files for the LDAP Mock API used in tests.

## Setup

This is based on the Node.js-powered [Mokapi][], which is available on Linux, 
Windows, macOS and more. Install it using the instructions on the website.

## Usage

Run the following terminal command to start the mock API:

```bash
./run-mock-server
```

Mokapi will display information on requests it receives in the terminal.
Additionally, a local web UI is available to check what's happening once 
it's running:

http://127.0.0.1:8080

[Mokapi]: https://mokapi.io/docs/resources/tutorials/mock-ldap-authentication-in-node
