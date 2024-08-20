jQuery(document).ready(function($) {
    // Function to update navigation links
    // function updateNavigationLinks() {
    //     $.ajax({
    //         url: 'check_auth.php',
    //         method: 'GET',
    //         success: function(response) {
    //             if (response.logged_in) {
    //                 $('.nav-link.profile').show().text('Profile');
    //                 $('.nav-link.logout').show().text('Logout');
    //                 $('.nav-link.login').hide();
    //                 $('.nav-link.register').hide();
    //             } else {
    //                 $('.nav-link.profile').hide();
    //                 $('.nav-link.logout').hide();
    //                 $('.nav-link.login').show().text('Login');
    //                 $('.nav-link.register').show().text('Register');
    //             }
    //         }
    //     });
    // }

    // Function to load content via AJAX
    function loadContent(_href, callback) {
        $.ajax({
            type: 'post',
            url: _href,
            success: function(data) {
                var data1 = $(data).filter(".mpage_container").html();
                var newTitle = $(data).filter("title").text(); // Get the new page title
                if (typeof (data1) == "undefined") { data1 = $(".mpage_container > *", data); }
                $(".mpage_container").html(data1);
                if (newTitle) {
                    document.title = newTitle; // Update the page title
                }
                unsaved = false;
                // updateNavigationLinks(); // Update navigation links after content load
                
                // Append footer to the container
                var footer = $(data).filter(".footer").html();
                if (footer) {
                    $(".footer").html(footer);
                }
                
                if (callback && typeof callback == "function") {
                    callback(data);
                }
            }
        });
    }

    // Handle AJAX links
    if (Modernizr.history) {
        history.replaceState({ myTag: true }, null, window.location.href);
    }
    
    $(document).on("click", "a.load_ajax", function(evt) {
        if (evt.which == 1) {
            if (!evt.ctrlKey && Modernizr.history) {
                var _href = $(this).attr("href");
                loadContent(_href, function(data) {
                    history.pushState({ myTag: true }, null, _href);
                });
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    });

    // Handle AJAX select
    $(document).on("change", "select.load_ajax", function(evt) {
        var _href = $(this).val();
        if (Modernizr.history) {
            loadContent(_href, function(data) {
                history.pushState({ myTag: true }, null, _href);
            });
            return false;
        } else {
            window.location.href = _href;
        }
    });

    // Handle popstate for AJAX navigation
    $(window).bind("popstate", function(e) {
        if (e.originalEvent.state && e.originalEvent.state.myTag) {
            var _href = location.href;
            loadContent(_href);
        }
    });

    // Update navigation links on initial load
    // updateNavigationLinks();
});
