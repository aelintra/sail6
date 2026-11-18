# LDAP contact add and tenant OU fixes

**Date:** 2026-11-17  
**Branch merged:** `vrev` → `main` (`306a64a`)  
**Repos:** `sail6` (legacy; see also sail65 / PBX3 for knock-on work)

## Summary

Contact adds via the Directory panel (`sarkldap`) were failing. Two code bugs in `ldaphelper::Add()`, plus missing per-tenant LDAP OUs for imported or older tenants, caused the failures. New tenant create also never created the matching LDAP OU.

## Symptoms

Syslog (example):

```text
ldap error with key=uid=...,ou=PDHCOTE,ou=contacts,dc=ec2,dc=internal, arg=Array
```

After improved logging:

```text
err=Invalid syntax
```

with an empty `o` (organisation) in the attribute array.

Non-`default` tenants failed to accept new contacts when the tenant OU did not exist under `ou=contacts`.

## Root causes

### 1. Missing `uid` on new contact entries

`Add()` built a DN of the form `uid=<uniqid>,ou=<tenant>,ou=contacts,<base>` but did not set the `uid` attribute on the entry. LDAP requires the RDN attribute to be present on the entry (naming violation / related failures).

The VCF import path (`generator/vcard_convert.php`) already set `uid` correctly; the panel add path did not.

### 2. Empty attribute values (`Invalid syntax`)

OpenLDAP rejects empty string attribute values. An empty organisation (`o`) produced `Invalid syntax`.

Contributing panel bug on older deployed `sarkldap/view.php` (fixed in-tree earlier):

```php
// WRONG — used phone for the empty check
if (isset($_POST['org']) && $_POST['phone'] != "") {
    $ldapargs["o"] = $_POST['org'];
}
```

Current `view.php` checks `$_POST['org']`. `Add()` now also strips empty string attributes defensively.

### 3. Missing tenant OUs

Contacts are stored under:

```text
uid=<id>,ou=<tenant>,ou=contacts,<LDAPBASE>
```

Tenant filtering in the UI is done by scoping the LDAP search/bind base in PHP (admin sees all under `ou=contacts`; non-admin uses `ou=<cluster>,ou=contacts`). It does **not** rely on per-tenant `simpleSecurityObject` entries.

Install LDIF creates base + `cn=admin` + `ou=contacts` only. Per-tenant OUs were not created on tenant add. `generator/fixupTenantLdap.php` can backfill OUs (and optionally security objects). Imported systems often only had `ou=default`.

`fixupTenantLdap.php` also calls `AddNewSimpleSecurityObject`. That path is optional for Directory UI behaviour; phone provisioning/`ldapropwd` is a separate concern. For OU-only repair, prefer calling `AddNewTenant` alone.

## Code changes

### `opt/sark/php/srkLDAPHelperClass`

- `Add()`: generate `$uid` once; set `$arg["uid"]`; remove empty string attributes before `ldap_add`.
- Error logging: include `ldap_error()` and `print_r($arg, true)`.
- `AddNewTenant()`: log LDAP error text; success message says tenant OU (not “contact”).

### `opt/sark/php/sarkcluster/view.php`

- After a successful cluster (tenant) insert, connect to LDAP and call `AddNewTenant($pkey)`.
- On LDAP connect/add failure, keep the DB tenant and append a warning to the UI message.
- **Does not** create per-tenant security objects.
- **Does not** delete the LDAP OU when a tenant is deleted (intentional for this release: tenants are rarely removed; deleting the OU would break entries still under that DN).

## Operational notes

### Repair existing systems

Create missing OUs for all clusters (OU only):

```bash
php -r '
include "/opt/sark/php/srkLDAPHelperClass";
include "/opt/sark/php/srkHelperClass";
include "/opt/sark/php/srkDbClass";
$helper = new helper;
$ldap = new ldaphelper;
if (!$ldap->Connect()) { fwrite(STDERR, "connect failed\n"); exit(1); }
foreach ($helper->getTable("cluster") as $row) {
  echo $row["pkey"] . ": " . $ldap->AddNewTenant($row["pkey"]) . "\n";
}
$ldap->Close();
'
```

Or run `php /opt/sark/generator/fixupTenantLdap.php` if you also want security objects (may fail or be unnecessary depending on environment).

### Deploy / verify

Copy at least:

- `/opt/sark/php/srkLDAPHelperClass`
- `/opt/sark/php/sarkcluster/view.php`
- `/opt/sark/php/sarkldap/view.php` (if the host still has the org/phone empty-check typo)

Verified on affected instance:

- Add contact under any tenant OU
- Leave organisation empty
- New tenant creates corresponding LDAP OU

## Follow-ups (other repos)

Tenant OU creation on tenant add (and any Directory/`ldaphelper` parity) likely needs the same treatment in **sail65** and **PBX3**. Do not assume those trees already create OUs or set `uid` on add.

## Files touched in this change set

| File | Change |
|------|--------|
| `opt/sark/php/srkLDAPHelperClass` | Contact add + logging + tenant OU message |
| `opt/sark/php/sarkcluster/view.php` | Create LDAP OU on new tenant |

`sarkldap/view.php` org empty-check was already correct on `main` for this repo; older deployed copies may still need that file updated.
