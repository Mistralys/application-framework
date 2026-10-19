# Synthesis Report — Media Document APIs: Rework 1

### Outcome Summary

This rework closed six follow-up items left by the parent Media Document APIs project: it added an upstream, configurable request-body size ceiling (`APP_API_MAX_REQUEST_BODY_SIZE`) enforced end-to-end from `AppConfig` through a new `RequestBodyReader` into `BaseAPIMethod::getRequestBody()`, with a client-facing 413/182802 error path; it closed the reachable inline-SVG-script risk by adding `MediaResponseHeaders` (nosniff for every served file, sandboxing CSP for SVG/SVGZ) to the shared `DocumentTrait` serving seam; it fixed a parameter-validation callback-argument bug and consolidated a duplicated raw-value accessor; it added committed test coverage for two previously-untested media fixtures; and it corrected the Media API README's error-code tables and size-limit documentation. All 8 work packages passed every stage of their pipelines (40 total PASS stages, 0 FAIL), with one pre-existing out-of-scope defect and one local-environment gap identified and left correctly untouched, both now flagged for follow-up.

### Metrics

- **Work packages:** 8/8 COMPLETE, 0 blocked/failed.
- **Pipeline stages:** 40/40 PASS across implementation, QA, security-audit, code-review, release-engineering, and documentation (pipeline_health reports 0 missing stages).
- **Tests:** Final exit gate (WP-008, independently re-run by QA): `composer analyze` 2237 files / 0 errors; `composer test-filter -- API` 749/749; `composer test-filter -- Media` 153/153 (2 pre-existing unrelated deprecations); `composer test-group -- live-http` 12/12 (81 assertions), including the previously-blocked oversized-body test once the local webserver's `post_max_size` was raised.
- **Security:** 3 security-audit stages run (WP-003, WP-004, WP-005), 0 Critical/High/Medium findings; the SVG-stored-XSS risk tracked in the sibling parent project's knowledge base is now closed and independently verified reachable/non-bypassable before the fix and non-bypassable after it.
- **Code review:** 0 blocking issues across all WPs; 1 reviewer-applied docblock fix (WP-003); several "gold-nugget" patterns recorded (see below).
- **Version:** Shipped under the existing WIP `v7.7.0` entry (no bump required — all changes are backwards-compatible additions/hardening).

### Strategic Recommendations

- **Overridable protected factory over preset-aware branching** (WP-004, `BaseAPIMethod::createRequestBodyReader()`): exposing a protected factory method so in-process tests can exercise a real code path at test-controlled limits, instead of adding test-only branching to the production class, is a reusable pattern whenever a boot-time setting needs to be exercised at a smaller scale from a test.
- **AC-grouped `// region:` test comments** (WP-004, `RequestBodyCeilingTest.php`): grouping test methods by acceptance-criterion ID makes the AC-to-test mapping legible without cross-referencing the WP description — worth adopting for other multi-AC test classes.
- **Bounded-read ceiling/discard/read-failure ordering** (WP-003, `RequestBodyReader`): the `maxBytes + 1` bounded `file_get_contents` plus a three-stage ceiling → discard → read-failure check ordering is a strong, reusable pattern for any future raw-input reader that must distinguish "too large," "silently discarded by PHP," and "genuinely unreadable" without leaking warnings.
- **Response headers over content sanitization for stored-XSS mitigation** (WP-005): choosing `nosniff` + a sandboxing CSP over SVG content sanitization kept the file byte-identical (no new parser dependency, no false positives, no effect on `<img>`-embedded SVG) while still closing the reachable risk — a pattern worth preferring whenever file byte-identity matters to downstream consumers.
- **Generated-docs pointer over hand-maintained tables** (WP-008): replacing two hand-maintained module tables (already 6–7 modules stale each) with a one-sentence pointer to the generated `modules-overview.md` removes a structurally recurring drift source rather than just fixing today's snapshot.

### Code Insights

**Developer:**
- WP-003: Suggested reusable test pattern — checking `error_reporting() & $errno` in a custom error handler to distinguish a correctly-suppressed `@`-operator warning from one that would actually surface, for any future "no warning leaked" assertion around suppressed I/O.
- WP-005: Flagged (high priority, out of scope) a pre-existing stray-token bug in `DocumentTrait::getMaxThumbnailSize()`.
- WP-006: Documented a parameter-validation limitation (`Base64FileParameter::resolveValue()` treats an empty decoded value as absent before the validator sees it) and the `ReflectionProperty` workaround used to still prove the `VALIDATION_EMPTY_FILE` (681004) response end-to-end — same limitation already documented elsewhere for an identical trait.

**QA:**
- WP-003: Recommended adding committed exact-boundary tests (`declaredLength == maxBytes`, `streamLength == maxBytes`, `maxBytes = 0`, `declaredLength = 0`) to `RequestBodyReaderTest.php`; verified correct behavior via a disposable scratch test but did not commit it.
- WP-005: Independently reconfirmed the pre-existing `DocumentTrait.php:32` stray-token bug via git-blame-style diff review.
- WP-006: Suggested a symmetry test (`test_htmlInKeywordsIsRejected`) for `MediaMetadataPieceTest`, since HTML rejection is currently proven only via the description field (shared code path, non-blocking).

**Security Auditor:**
- WP-003: Flagged the public `$streamURI` constructor parameter as an SSRF-adjacent design risk once a live caller is wired (addressed in WP-004's own audit, confirmed non-issue since only `fromGlobals()`/`php://input` populate it in practice); also noted no audit-trail emission on oversized/discarded-body rejection (low, non-blocking).
- WP-005: Noted as an accepted, documented trade-off that a "headers already sent" condition logs but does not abort serving.

**Reviewer:**
- WP-003: Applied a docblock wording fix (removed a premature "sole caller" claim ahead of WP-004's wiring).
- WP-005 / WP-001: Independently re-confirmed via git blame that the pre-existing `DocumentTrait::getMaxThumbnailSize()` bug predates this project (2023-11-03 commit), correctly left untouched.

**Documentation:**
- WP-001, WP-003, WP-004, WP-007: Repeatedly found generated `.context/` CTX docs stale relative to source changes (even after a prior WP's own regen pass) and closed the gap via `ctx-architect` delegation each time — this recurred across 4 of 8 WPs, suggesting the CTX regen trigger condition may be worth revisiting project-wide.

### Deferred & Follow-Up Items

- **Pre-existing bug in `DocumentTrait::getMaxThumbnailSize()`** — *out-of-scope* (confirmed pre-existing, unrelated to this plan). Source: WP-005 (also independently flagged in WP-001's review). Originating agents: Developer, QA, Security Auditor, Reviewer (all four, independently). Description: `src/classes/Application/Media/DocumentTrait.php:34` reads `return $this-$this->getThumbnailSourceImage()->getWidth();` — a stray-token copy-paste defect (confirmed via git blame to a 2023-11-03 commit) that will throw a `TypeError` (object minus object) at runtime instead of returning the intended width. Priority: high (per QA/Developer bug comments). Recommendation (from project comment by Reviewer): file this as its own bug ticket/WP outside this plan.
- **Local test-environment `post_max_size` gap** — *out-of-scope* (environment configuration, not a code defect; already documented in the plan's own Human Actions table). Source: WP-004 and WP-008 project-level incident comments, originating agents Developer and QA. Description: the local Apache/PHP test webserver's `post_max_size` (8 MiB) sits below the framework's derived request-body ceiling (~14.3 MiB), causing PHP's own request-startup check to discard oversized bodies before the app boots, which blocked `test_oversizedBodyIsRejectedOverHTTP` until WP-008 worked around it locally with an untracked `tests/application/.htaccess` (`post_max_size=32M`), not committed. Priority: medium. Recommendation: raise `post_max_size` to at least the effective ceiling on actual target installs, as already documented in `Media/API/README.md` and the plan's Human Action list; the untracked `.htaccess` workaround should not be relied upon beyond this session's local verification.
- **HTML-in-keywords test symmetry** — *deferred* (non-blocking nice-to-have). Source: WP-006. Originating agent: QA (confirmed reasonable by Reviewer). Description: `MediaMetadataPieceTest` only directly tests HTML rejection via the `description` field, not `keywords`, though both share the identical `validateNoHTML()` code path. Priority: low.
- **Exact-boundary coverage for `RequestBodyReader`** — *deferred* (non-blocking hardening). Source: WP-003. Originating agent: QA. Description: committed test suite lacks explicit boundary cases (`declaredLength == maxBytes`, `streamLength == maxBytes`, `maxBytes = 0`, `declaredLength = 0`); correctness was independently verified via a disposable, deleted scratch test but not committed. Priority: low, recommended as a future hardening pass.
- **No audit-trail on rejected oversized/discarded request bodies** — *deferred* (hardening observation, not a finding). Source: WP-003 and WP-004 security-audit stages. Originating agent: Security Auditor. Description: no logging is emitted when a body is rejected for exceeding the ceiling or for being silently discarded by `post_max_size`. Priority: low.

### Next Steps

- File the pre-existing `DocumentTrait::getMaxThumbnailSize()` bug as its own WP/ticket — it is a confirmed runtime defect independent of this plan and was flagged by four separate agents.
- Confirm `post_max_size` (and any web-server body limit) is raised to at least the effective request-body ceiling on real target installs before relying on the new 413/182802 behavior in production; do not carry forward the untracked local `.htaccess` workaround.
- Consider a small hardening follow-up WP covering the two low-priority test-coverage gaps (`RequestBodyReader` exact-boundary cases, `MediaMetadataPieceTest` HTML-in-keywords symmetry) and optional audit-trail logging for rejected oversized/discarded bodies.
- Investigate why generated `.context/` CTX docs went stale relative to source changes across 4 of 8 WPs in this project, even after prior regen passes — the regen trigger/condition may need a project-wide review to avoid this recurring documentation-drift pattern.

---

## AX Feedback

No friction encountered.
