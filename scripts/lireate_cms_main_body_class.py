"""Safe <body class=\"cms-default…\"> conditional snippets for CMS main.php."""

CMS_DEFAULT = "cms-default"

DOCS_BODY_COND = "<?php if($temp['page']=='docs') echo ' lireate-docs-body';?>"
CONTACTS_BODY_COND = "<?php if($temp['page']=='kontaktyi') echo ' lireate-contacts-body';?>"


def _insert_after_pos(text, pos, snippet):
    if snippet in text:
        return text, False
    if pos < 0:
        return text, False
    return text[:pos] + snippet + text[pos:], True


def cms_default_insert_pos(text):
    idx = text.find(CMS_DEFAULT)
    if idx == -1:
        return -1
    return idx + len(CMS_DEFAULT)


def patch_docs_body_conditional(text):
    """Insert docs conditional immediately after literal cms-default."""
    if DOCS_BODY_COND in text:
        return text, False
    pos = cms_default_insert_pos(text)
    return _insert_after_pos(text, pos, DOCS_BODY_COND)


def patch_contacts_body_conditional(text):
    """Insert contacts conditional after docs snippet if present, else after cms-default."""
    if CONTACTS_BODY_COND in text:
        return text, False
    if DOCS_BODY_COND in text:
        pos = text.find(DOCS_BODY_COND) + len(DOCS_BODY_COND)
    else:
        pos = cms_default_insert_pos(text)
    return _insert_after_pos(text, pos, CONTACTS_BODY_COND)


def remove_body_conditional_snippets(text, snippets):
    restored = text
    for snippet in snippets:
        restored = restored.replace(snippet, "")
    return restored


def remove_static_body_class(text, class_name):
    """Legacy rollback: strip static class name from body class attribute."""
    return (
        text.replace(" " + class_name, "")
        .replace(class_name + " ", "")
        .replace('class="' + class_name + '"', 'class="cms-default"')
    )
