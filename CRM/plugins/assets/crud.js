jQuery(document).ready(function($) {
    let lastId = $('.lead_id').last().val() || "0000";

    function getNextId() {
        lastId = (parseInt(lastId, 10) + 1).toString().padStart(4, '0'); 
        return lastId;
    }

    $('#add_field').on('click', function() {
        let newId = getNextId();

        let newField = `
        <div class="col-6 d-flex mt-5 group-fields"> 
            <div class="col-6 row">
                <div class="col-3"> 
                    <label class="form-label" for="lead_name">
                        <p class="fw-medium">Name</p>
                    </label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" name="lead_name[]" value="">
                </div>

                <div class="col-3"> 
                    <label class="form-label" for="lead_type">
                        <p class="fw-medium">Type</p>
                    </label>
                </div>
                <div class="col-9">
                    <select class="form-control" name="lead_type[]">
                        <option value="empty">Select type</option>
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="list">List</option>
                    </select>
                </div>

                <div class="col-3"> 
                    <label class="form-label" for="lead_value">
                        <p class="fw-medium">Value</p>
                    </label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control" name="lead_value[]" value="">
                </div>

                <div class="col-3"> 
                    <label for="lead_id">
                        <p class="fw-medium">ID</p>
                    </label>
                </div>
                <div class="col-9">
                    <input type="text" class="form-control lead_id" name="lead_id[]" value="${newId}" readonly>
                </div>
            </div>

            <div class="col-2 mt-2">
                <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
                    <input class="form-control check-field" type="checkbox" name="check[]">
                </div>
            </div>
        </div>`;

        $('.group-fields').last().after(newField);
    });

    $('#delete_field').on('click', function() {
        $('.group-fields').each(function() {
            var checkbox = $(this).find('.check-field').prop('checked');
            if (checkbox) {
                $(this).remove();
            }
        });
    });
});
