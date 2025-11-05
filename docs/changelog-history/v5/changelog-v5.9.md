## v5.9.0 - Country management (Breaking-M)
- Wizards: Added the possibility to specify step classes, loosening the class structure.
- Countries: Added the country management screens.
- Countries: Added user rights to manage countries.
- Countries: Improved country ISO code alias handling (`uk` and `gb`).
- CacheControl: Added the localization cache to the cache control.
- DBHelper: `statementValues()` now accepts an existing instance.
- ListBuilder: Added `_renderAboveList()` and `_renderBelowList()` to the list builder screen trait.
- Dependencies: Updated AppUtils Core to [v2.3.11](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.11).
- Dependencies: Updated AppUtils Core to [v2.3.12](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.12).
- Dependencies: Updated AppUtils Core to [v2.3.13](https://github.com/Mistralys/application-utils-core/releases/tag/2.3.13).
- Dependencies: Updated AppUtils Collections to [v1.1.5](https://github.com/Mistralys/application-utils-collections/releases/tag/1.1.5).
- Dependencies: Updated AppUtils Collections to [v1.1.6](https://github.com/Mistralys/application-utils-collections/releases/tag/1.1.6).
- Dependencies: Updated AppLocalize to [v2.0.0](https://github.com/Mistralys/application-localization/releases/tag/2.0.0).

### Breaking changes

The feature to ignore countries has been permanently retired.
If your application uses this feature, you must remove the
related method calls. We recommend using a static analysis
tool like PHPStan to find all usages.

I decided to retire it because while it was a quick solution
for some use cases, it was not stable enough and caused issues
in other situations. The application must decide for itself
which countries are relevant depending on the use case.
