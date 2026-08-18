//EmailBundle (Copied from app/bundles/LeadBundle/Assets/js/lead.js)
MailVotech.emailBatchSubmit = function() {
    if (MailVotech.batchActionPrecheck("")) {
        if (mQuery('#email_batch_newCategory').val()) {
            const $emailBatchIds = mQuery('#email_batch_ids');
            if ($emailBatchIds.length) {
                $emailBatchIds.val(MailVotech.getCheckedListIds(false, true));
            }

            return true;
        }

    }

    return false;
};

function setCategory(id, newCategory) {
    const tr = document.querySelector("#row_email_" + id);
    const div = tr.querySelector("div.d-flex.ai-center.gap-xs");
    const span = div.querySelector("span");

    div.textContent = newCategory.name;
    span.style = "background: #" + newCategory.color + ";"

    div.prepend(span);
}

MailVotech.emailBatchSubmitCallback = function( response ) {
    mQuery('#MailVotechSharedModal').modal('hide');
    console.log("Received: " + JSON.stringify(response));
    response.affected.forEach( function(id){
        setCategory(id, response.newCategory);
    });
}