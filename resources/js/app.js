require('./bootstrap');
require('./laravel');

const REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' + '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';
const timezone = jstz.determine();

function readImageAsURL(input) {
    if (input.files && input.files[0]) {
        // >100 MB is not allowed
        if (input.files[0].size > 104857600) {
            toastr.error('The file exceeds 100MB limit.');

            $(input).val('');

            return false;
        }

        var reader = new FileReader();

        reader.onload = function (e) {
            // If flyer is a video...
            if (e.target.result.lastIndexOf('data:video', 0) === 0) {
                var $source = $("video.ImageInputWithPreview__picture[data-for='"+input.id+"'] source");
                $source[0].src = URL.createObjectURL(input.files[0]);
                $source.parent()[0].load();

                $("div.ImageInputWithPreview__picture[data-for='"+input.id+"']").hide();
                $("video.ImageInputWithPreview__picture[data-for='"+input.id+"']").show();
            } else {
                $(".ImageInputWithPreview__picture[data-for='"+input.id+"']").css('background-image', "url('"+e.target.result+"')");
            }

            $(".ImageInputWithPreview__picture[data-for='"+input.id+"']").attr('img-loaded', true);
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function initSelectize() {
    $('select.selectize').selectize();
}

function initSelectizePrivacy() {
    // Only when 'public' option is disabled
    $('select.selectize-followout-privacy').selectize({
        onChange: function(value) {
            var selectize = $(this)[0];
            if (value == 'public') {
                $('#followout-cant-be-public-modal').modal('show');
                selectize.setValue('private');
            }
        },
    });
}

function initSelectizeContact() {
    $('select.selectize-contact').selectize({
        persist: true,
        create: true,
        valueField: 'email',
        labelField: 'name',
        searchField: ['name', 'email'],
        options: [],
        render: {
            item: function(item, escape) {
                return '<div>' +
                    (item.name ? '<span class="name">' + escape(item.name) + '</span>' : '') +
                    (item.email ? '<span class="email">' + escape(item.email) + '</span>' : '') +
                '</div>';
            },
            option: function(item, escape) {
                var label = item.name || item.email;
                var caption = item.name ? item.email : null;
                return '<div>' +
                    '<span class="label">' + escape(label) + '</span>' +
                    (caption ? '<span class="caption">' + escape(caption) + '</span>' : '') +
                '</div>';
            }
        },
        createFilter: function(input) {
            var match, regex;

            // email@address.com
            regex = new RegExp('^' + REGEX_EMAIL + '$', 'i');
            match = input.match(regex);
            if (match) return !this.options.hasOwnProperty(match[0]);

            // name <email@address.com>
            regex = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
            match = input.match(regex);
            if (match) return !this.options.hasOwnProperty(match[2]);

            return false;
        },
        create: function(input) {
            if ((new RegExp('^' + REGEX_EMAIL + '$', 'i')).test(input)) {
                return {email: input};
            }
            var match = input.match(new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i'));
            if (match) {
                return {
                    email : match[2],
                    name  : $.trim(match[1])
                };
            }

            return false;
        }
    });
}

function initSelectizeContacts() {
    $('select.selectize-contacts').selectize({
        persist: true,
        create: true,
        maxItems: null,
        valueField: 'email',
        labelField: 'name',
        searchField: ['name', 'email'],
        options: [],
        render: {
            item: function(item, escape) {
                return '<div>' +
                    (item.name ? '<span class="name">' + escape(item.name) + '</span>' : '') +
                    (item.email ? '<span class="email">' + escape(item.email) + '</span>' : '') +
                '</div>';
            },
            option: function(item, escape) {
                var label = item.name || item.email;
                var caption = item.name ? item.email : null;
                return '<div>' +
                    '<span class="label">' + escape(label) + '</span>' +
                    (caption ? '<span class="caption">' + escape(caption) + '</span>' : '') +
                '</div>';
            }
        },
        createFilter: function(input) {
            var match, regex;

            // email@address.com
            regex = new RegExp('^' + REGEX_EMAIL + '$', 'i');
            match = input.match(regex);
            if (match) return !this.options.hasOwnProperty(match[0]);

            // name <email@address.com>
            regex = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
            match = input.match(regex);
            if (match) return !this.options.hasOwnProperty(match[2]);

            return false;
        },
        create: function(input) {
            if ((new RegExp('^' + REGEX_EMAIL + '$', 'i')).test(input)) {
                return {email: input};
            }
            var match = input.match(new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i'));
            if (match) {
                return {
                    email : match[2],
                    name  : $.trim(match[1])
                };
            }

            return false;
        }
    });
}

function initPickatime() {
    $('.timepicker').pickatime({
        format: 'hh:i A',
        interval: 15,
        clear: '',
    });
}

function initPickadate() {
    $('.datepicker').pickadate({
        format: 'mm/dd/yyyy',
        clear: '',
        // selectMonths: true,
        // selectYears: true,
    });
}

// Inputs
$(document).ready(function() {
    Cookies.set('timezone', timezone.name());

    $('[data-toggle="tooltip"]').tooltip();

    initSelectize();
    initSelectizePrivacy();
    initSelectizeContact();
    initSelectizeContacts();

    $('.ProfilePictureThumbs__thumb').not('.ProfilePictureThumbs__thumb--followout').on('click', function() {
        $(".ProfilePictureThumbs__thumb").removeClass('active');
        $(this).addClass('active');
        $(".ProfilePicture__picture").attr('src', $(this).attr('src'));
    });

    $('.ImageInputWithPreview__input').on('change', function() {
        readImageAsURL(this);
    });

    $('.ImageInputWithPreview__picture').on('click', function() {
        var input = $('#' + $(this).data('for'));

        if ($(this).is("[data-picture-id]")) {
            var id = $(this).attr('data-picture-id');
            $("select[name^=removed_pictures] option[value="+id+"]").attr('selected', 'true').change();
            $(".ImageInputWithPreview__picture[data-for='"+input.attr('id')+"']").css('background-image', '').attr('img-loaded', false);
            $(this).removeAttr('data-picture-id');
        } else if ($(this).is("[followout-flyer-id]")) {
            var id = $(this).attr('followout-flyer-id');
            $("select[name^=removed_flyer] option[value="+id+"]").attr('selected', 'true').change();
            $(".ImageInputWithPreview__picture[data-for='"+input.attr('id')+"']").css('background-image', '').attr('img-loaded', false);
            $(this).removeAttr('followout-flyer-id');

            // If it's a video flyer...
            if ($(this).hasClass('ImageInputWithPreview__picture--video')) {
                $(this).removeClass('ImageInputWithPreview__picture--video-processing');
                $(this).closest('.ImageInputWithPreview').removeClass('ImageInputWithPreview--video-processing');
                $(this).removeAttr('src');
                $(this).find('source').removeAttr('src');
                $(this).hide();
                $(this).parent().find('.ImageInputWithPreview__picture').not('.ImageInputWithPreview__picture--video').first().show();
            }
        } else if ($(this).is("[followout-picture-id]")) {
            var id = $(this).attr('followout-picture-id');
            $("select[name^=removed_pictures] option[value="+id+"]").attr('selected', 'true').change();
            $(".ImageInputWithPreview__picture[data-for='"+input.attr('id')+"']").css("background-image", '').attr('img-loaded', false);
            $(this).removeAttr('followout-picture-id');
        } else if ($(this).attr('img-loaded') === 'true') {
            input.replaceWith(input.val('').clone(true));
            $(".ImageInputWithPreview__picture[data-for='"+input.attr('id')+"']").css("background-image", '').attr('img-loaded', false);

            // If it's a video flyer...
            if ($(this).hasClass('ImageInputWithPreview__picture--video')) {
                $(this).removeClass('ImageInputWithPreview__picture--video-processing');
                $(this).closest('.ImageInputWithPreview').removeClass('ImageInputWithPreview--video-processing');
                $(this).removeAttr('src');
                $(this).find('source').removeAttr('src');
                $(this).hide();
                $(this).parent().find('.ImageInputWithPreview__picture').not('.ImageInputWithPreview__picture--video').first().show();
            }
        } else {
            $("#"+$(this).data('for')).click();
        }
    });

    initPickatime();
    initPickadate();
});

// Add native browser tooltip for long names
$(document).on('mouseenter', '.ProfileLinkedItems__name', function() {
    var $e = $(this);
    var title = $e.attr('title');

    if (!title) {
        if (this.offsetWidth < this.scrollWidth) {
            $e.attr('title', $e.text());
        } else {
            if (this.offsetWidth >= this.scrollWidth && title == $e.text()) {
                $e.removeAttr('title');
            }
        }
    }
});

$(document).on('click', '.NotificationListItem__mark-read-button', function() {
    var markReadButton = $(this);
    var url = $(this).data('href');
    var item = $(this).closest('.NotificationListItem');

    $.ajax({
        url: url,
        type: 'GET',
        beforeSend: function (xhr) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + Laravel.api_token);

            item.removeClass('NotificationListItem--unread');

            // Remove 'Mark as read' button
            markReadButton.remove();
        },
        success: function(response) {
            // Remove 'unread' icon from header
            if (response.data.unread_count == 0) {
                $('.Header__nav-item .has-unread-notifications-icon').remove();
            }
        }
    });
});

$(document).on('click', '#invite-friends-modal-form-add-invitee-btn', function() {
    var inputs = $('#invite-friends-modal-form-invites');
    var $template = $('#invite-friends-modal-form-add-invitee-template').children().clone();

    inputs.append($template);
});
