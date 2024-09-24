$(document).ready(function () {
    // Load navbar content dynamically
    $('#navbar').load('./views/components/navbar.html', function() {
        // Event listener for the "Font" button click
        $(document).on('click', '#font', function(e) {
            e.preventDefault();
            loadFontUpload();
        });

        // Load font page
        loadFontUpload();

        // Load font groups page
        $(document).on('click', '#fontGroups', function(e) {
            e.preventDefault();
            loadFontGroupUpload();
        });
    });

    // Load font
    function loadFontUpload() {
        $('#content').load('./views/components/font_main.html', function () {

            $('#uploadNewFont').on('click', function () {
                $('#uploadSection').toggleClass('hidden');
            });

            // list of font
            $.ajax({
                url: 'http://localhost:8000/api/fonts',
                type: 'GET',
                processData: false,
                contentType: false,
                success: function(response) {
                    // TODO: show success notification

                    let fontTableBody = '';
                    response.forEach(function(font) {
                        fontTableBody += `
                                            <tr>
                                                <td class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                    ${font.name}
                                                </td>
                                                <td class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                    <span style="font-family: '${font.name.split('.')[0]}';">Preview</span>
                                                </td>
                                                <td class="px-6 py-4 text-sm font-medium leading-5 text-right whitespace-no-wrap border-b border-gray-200">
                                                    <button class="delete-font bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded" data-id="${font.id}">
                                                        DELETE
                                                    </button>
                                                </td>
                                            </tr>
                                        `;
                    });

                    $('table tbody').html(fontTableBody);
                },
                error: function(xhr, status, error) {
                    // TODO: show error notification
                }
            });

            // upload font
            $('#uploadFile').on('change', function () {
                const formData = new FormData();
                const fileInput = $('#uploadFile')[0].files[0];

                // Check if the file is a TTF file
                if (fileInput && fileInput.name.endsWith('.ttf')) {
                    formData.append('font', fileInput);

                    // Send the file via AJAX to the store API
                    $.ajax({
                        url: 'http://localhost:8000/api/fonts',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            $('#upload-status').html('<p class="text-blue-500">Uploading...</p>');
                        },
                        success: function(response) {
                            // TODO: show success notification
                            let newFontRow = `
                                                <tr>
                                                    <td class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                        ${response.data.name}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
                                                        <span style="font-family: '${response.data.name.split('.')[0]}';">Preview</span>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm font-medium leading-5 text-right whitespace-no-wrap border-b border-gray-200">
                                                        <button class="delete-font bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded" data-id="${response.data.id}">
                                                            DELETE
                                                        </button>
                                                    </td>
                                                </tr>
                                            `;

                            $('table tbody').append(newFontRow);
                        },
                        error: function(xhr, status, error) {
                            // TODO: show error notification
                        }
                    });
                } else {
                    // TODO: show error notification
                }
            });

            // delete font
            $(document).on('click', '.delete-font', function() {
                const fontId = $(this).data('id');

                // Confirm delete action
                if (confirm('Are you sure you want to delete this font?')) {
                    $.ajax({
                        url: `http://localhost:8000/api/fonts/${fontId}`,
                        type: 'DELETE',
                        success: function(response) {
                            // Remove the font row from the table
                            $(this).closest('tr').remove();
                            alert(response.message);
                        }.bind(this),
                        error: function(xhr, status, error) {
                            alert('Error deleting font: ' + error);
                        }
                    });
                }
            });
        });
    }

    // Load font groups
    function loadFontGroupUpload() {
        $('#content').load('./views/components/font_group_main.html', function () {

            $('#newFontGroupBtn').on('click', function () {
                $('#font-group-form').toggleClass('hidden');
            });

            // Load fonts into the select options for the initial row
            fetchFonts();

            // Add a new row on click
            $('#add-row-btn').click(function() {
                const newRow = `
                <div class="mb-4 flex items-center space-x-4">
                    <input type="text" placeholder="Enter font name" class="w-1/2 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                    <select class="font-select w-1/2 px-4 py-2 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select font</option>
                    </select>

                    <button type="button" class="remove-row text-red-500 font-bold text-xl">&times;</button>
                </div>
            `;
                $('#font-rows').append(newRow);
                // Fetch fonts for the new select element
                fetchFontsForNewRow($('#font-rows').find('.font-select').last());
            });

            // Fill font name input based on selected font
            $(document).on('change', '.font-select', function() {
                const fontName = $(this).find('option:selected').text();
                $(this).closest('.flex').find('input[type="text"]').val(fontName);
            });

            // Remove row on click
            $('#font-rows').on('click', '.remove-row', function() {
                $(this).closest('.flex').remove();
            });

            // Get font groups
            $.ajax({
                url: 'http://localhost:8000/api/font-groups',
                type: 'GET',
                processData: false,
                contentType: false,
                success: function(response) {
                    let fontTableBody = '';

                    // Loop through each font group
                    JSON.parse(response).forEach(function(group) {
                        // Prepare the fonts as a comma-separated list
                        let fontsList = group.items.map(function(item) {
                            return item.font_name; // Assuming 'font_name' is in the response
                        }).join(', ');

                        // Generate the table row for each font group
                        fontTableBody += `
                            <tr>
                                <!-- Font Group Name -->
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    ${group.group_name}
                                </td>

                                <!-- Fonts in the group -->
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    ${fontsList}
                                </td>

                                <!-- Count of Fonts in the group -->
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    ${group.items.length}
                                </td>

                                <!-- Action Column (optional for future use) -->
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    <button class="delete-font-group px-4 py-2 text-xs font-medium text-white bg-red-500 rounded" data-id="${group.id}">
                                        Delete
                                    </button>
                                </td>
                            </tr>`;
                    });

                    // Populate the table body with the generated rows
                    $('table tbody').html(fontTableBody);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                    // TODO: show error notification
                }
            });

            // Submit font group data
            $('#font-group-form').on('submit', function(e) {
                e.preventDefault();

                const groupName = $('#group-name').val();
                const fontData = [];

                $('#font-rows .flex').each(function() {
                    const name = $(this).find('input[type="text"]').val();
                    const id = $(this).find('.font-select').val();
                    fontData.push({ name: name, id });
                });

                if (groupName && fontData.length > 1) {
                    $.ajax({
                        url: 'http://localhost:8000/api/font-groups', // Adjust the URL as needed
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ groupName, fonts: fontData }),
                        success: function(response) {
                            let newFontGroupRow = '';

                            // Generate the table row for each font group
                            newFontGroupRow += `
                            <tr>
                                <!-- Font Group Name -->
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    ${JSON.parse(response).data.name}
                                </td>

                                <!-- Fonts in the group -->
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    ${JSON.parse(response).data.fonts}
                                </td>

                                <!-- Count of Fonts in the group -->
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    ${JSON.parse(response).data.count}
                                </td>

                                <!-- Action Column (optional for future use) -->
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    <button class="delete-font-group px-4 py-2 text-xs font-medium text-white bg-red-500 rounded" data-id="${JSON.parse(response).data.id}">
                                        Delete
                                    </button>
                                </td>
                            </tr>`;

                            $('table tbody').append(newFontGroupRow);
                        },

                        error: function(xhr, status, error) {
                            alert('Error creating font group: ' + error);
                        }
                    });
                } else {
                    alert('Please ensure you have a group name and at least two fonts selected.');
                }
            });

            // Delete font group
            $(document).on('click', '.delete-font-group', function() {
                const fontGroupId = $(this).data('id');

                // Confirm delete action
                if (confirm('Are you sure you want to delete this font group?')) {
                    $.ajax({
                        url: `http://localhost:8000/api/font-groups/${fontGroupId}`,
                        type: 'DELETE',
                        success: function(response) {
                            // Remove the font row from the table
                            $(this).closest('tr').remove();
                            alert(JSON.parse(response).message);
                        }.bind(this),
                        error: function(xhr, status, error) {
                            alert('Error deleting font: ' + error);
                        }
                    });
                }
            });
        });
    }

    // fetch fonts and populate select options
    function fetchFonts() {
        $.ajax({
            url: 'http://localhost:8000/api/fonts', // Adjust the URL as needed
            type: 'GET',
            success: function(response) {
                const selectOptions = response.map(font => `<option value="${font.id}">${font.name}</option>`).join('');
                $('.font-select').html(`<option value="">Select font</option>${selectOptions}`);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching fonts:', error);
            }
        });
    }

    // fetch fonts for a newly added row
    function fetchFontsForNewRow(selectElement) {
        $.ajax({
            url: 'http://localhost:8000/api/fonts', // Adjust the URL as needed
            type: 'GET',
            success: function(response) {
                const selectOptions = response.map(font => `<option value="${font.id}">${font.name}</option>`).join('');
                selectElement.html(`<option value="">Select font</option>${selectOptions}`);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching fonts:', error);
            }
        });
    }
});