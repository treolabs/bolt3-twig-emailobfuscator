Bolt CMS 3 - Twig Email Obfuscator
======================

This extension boasts Bolt CMS 3 with an additional Twig filter to obfuscate email addresses. It rests on the well established method described [here](http://rot13.florianbersier.com) using Javascript

To use this extension, just add `obfuscateEmail` as a filter next to any text-containing field.

E.g.

    {{ record.body|obfuscateEmail }}

Obfuscates emails in the plain text:

    test@test.com

in anchors (mailto):

    <a href="mailto:test@test.com">send email</a>

in anchors (mailto and label):

    <a href="mailto:test@test.com">test@test.com</a>
