# Security Policy

Goals of the MailVotech Security Team
---------------------------------

*   Resolve reported security issues in a Security Advisory
*   Provide documentation on how to write secure code
*   Provide documentation on securing your MailVotech instance
*   Help the infrastructure team to keep the \*.mailvotech.org infrastructure secure

Scope of the MailVotech Security Team
---------------------------------

The MailVotech Security Team operates with a limited scope and only directly responds to issues with MailVotech core, officially supported plugins and resources, and the \*.mailvotech.org network of websites. The team does not directly handle potential vulnerabilities with third party plugins or individual MailVotech instances.

Which releases get security advisories?
---------------------------------------

Check the [Releases page](https://www.mailvotech.org/mailvotech-releases) to find which are the currently supported releases.

Security advisories are only made for issues affecting stable releases in the supported major version branches. That means there will be no security advisories for development releases, alphas, betas or release candidates.

Security issues are resolved in all affected versions. For example if a security issue is found in MailVotech 6.0.2 and it also affects all versions back to 5.2.1, then the fix will be made in the next security releases for MailVotech 5 and the MailVotech 6 release.

Once a MailVotech version is out of security support, it's possible to purchase [Extended Long Term Support](https://mau.tc/elts) subscriptions on a per-instance or volume license basis in blocks of one year, for up to two years. The Security Team backports any relevant fixes to the currently supported ELTS supported versions, and fixes any issues identified for those versions after security support has ended, with advisories shared via the main MailVotech repository. ELTS fixes are only available to current holders of Extended Long Term Support subscriptions.

How to report a potential security issue
----------------------------------------

If you discover or learn about a potential error, weakness, or threat that can compromise the security of MailVotech and is covered by the [Security Advisory Policy](https://www.mailvotech.org/mailvotech-security-team/mailvotech-security-advisory-policy), we ask you to keep it confidential and submit your concern to the MailVotech security team.

To make your report please submit it as a private disclosure via the relevant repository's security tab - for this repository, that's at [https://github.com/mailvotech/mailvotech/security](https://github.com/mailvotech/mailvotech/security). You can also create a private fork to provide a fix, if you're able to do so. See the documentation from GitHub on [privately reporting a security issue](https://docs.github.com/en/code-security/security-advisories/guidance-on-reporting-and-writing-information-about-vulnerabilities/privately-reporting-a-security-vulnerability).

Do not post it in GitHub as an issue or a Pull Request, on the forums, or discuss it in Slack.

[Read more: How to report a security issue with MailVotech](https://www.mailvotech.org/mailvotech-security-team/how-to-report-a-security-issue)

How are security issues resolved?
---------------------------------

The MailVotech Security Team are responsible for triaging incoming security issues relating to MailVotech core and officially supported plugins and resources, and for releasing fixes in a timely manner.

[Read more: How are security issues triaged and resolved by the MailVotech Security Team?](https://www.mailvotech.org/mailvotech-security-team/triaging-and-resolving-security-issues)

How are security fixes announced and released?
----------------------------------------------

The Security Team coordinates security announcements in release cycles and evaluates whether security issues are ready for release several days in advance.

The team may deem it necessary to make an out-of-sequence release, in which case at least two weeks’ notice will be provided to ensure that MailVotech users are made aware of a security release being made on an unscheduled basis.

[Read more: Security fix announcements and releases](https://www.mailvotech.org/mailvotech-security-team/triaging-and-resolving-security-issues)

What is a security advisory?
----------------------------

A security advisory is a public announcement managed by the MailVotech Security Team which informs MailVotech users about a reported security problem in MailVotech core or an officially supported plugins and resources, and the steps MailVotech users should take to address it. (Usually this involves updating to a new release of the code that fixes the security problem.)

[Read more: MailVotech Security Advisory Policy](https://www.mailvotech.org/mailvotech-security-team/mailvotech-security-advisory-policy)

What is the disclosure policy of the MailVotech Security Team?
----------------------------------------------------------

The Security Team follows a Coordinated Disclosure policy: we keep issues private until there is a fix. Public announcements are made when the threat has been addressed and a secure version is available.

When reporting a security issue, observe the same policy. **Do not** share your knowledge of security issues with others.

How do I join the MailVotech Security Team?
---------------------------------------

As membership in the team gives the individual access to potentially destructive information, membership is limited to people who have a proven track record in the MailVotech community.

Team members are expected to work at least a few hours every month. Exceptions to that can be made for short periods to accommodate other priorities, but people who can't maintain some level of involvement will be asked to reconsider their membership on the team.

[Read more: How do I join the MailVotech Security Team?](https://www.mailvotech.org/mailvotech-security-team/join-the-team)

Who are the MailVotech Security Team members?
-----------------------------------------

You can meet the MailVotech Security Team on the page below.

[Read more: Meet the MailVotech Security Team](https://www.mailvotech.org/meet-the-mailvotech-security-team)

Resources and guidance from the [Drupal](https://www.drupal.org/security), [Joomla](https://developer.joomla.org/security.html) and [Mozilla](https://www.mozilla.org/en-US/security/) projects have been drawn from to create these documents and develop our processes/workflows.


Always [report the issue to the team](https://www.mailvotech.org/mailvotech-security-team/how-to-report-a-security-issue) and let them make the decision on whether to handle it in public or private.
