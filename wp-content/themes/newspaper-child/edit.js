


jQuery(function ($) {

    addConfigurationOptions();
    addCategoryToFlexBoxes();
    initiateDragAndDrop(postMoved);

    function addCategoryToFlexBoxes() {
        $flexBoxes = $('.jsFlexBox');
        
        for (var i = 0; i < $flexBoxes.length; i++) {
            
            $($flexBoxes[i]).attr("data-category-slug", window.currentPageBoxes[i].category.slug);
            
            const flexBox = $($flexBoxes[i]);

            // Gå op til parent .wpb_wrapper
            const wrapper = flexBox.closest('.wpb_wrapper');

            // Find den tilhørende .jsFlexBoxTitle inden i samme wrapper
            const title = wrapper.find('.jsFlexBoxTitle');

            // Fx læs titlen:
            const titleEl = title.find('.td-pulldown-size');
            $(titleEl).text(window.currentPageBoxes[i].category.name);
        }
    }

    function addConfigurationOptions() {

        $.post(myAjax.ajaxurl, {
            action: 'get_post_category_list',
            nonce: myAjax.nonce
        }, function (response) {
            if (response.success) {
                window.postListCategories = response.data.data;

                //  Add configuration to every post
                $('.showInfo').remove();
                let html = '<a href="javascript:void(0)" class="showInfo" style="margin-left:10px;cursor:grab;"><span{alert} class="postInfo" data-post-id="{id}" data-category-slug="{slug}">⚙</span></a>';
                $('.td-cpt-post').each(function (index, element) {
                    let postId = getPostId(element);
                    $(element).attr('data-post-id', postId);
                    $(element).addClass("isPost");

                    const category = $(this).closest('.jsFlexBox').attr("data-category-slug");
                    htmlToInsert = getConfigHtml(html, postId, category);
                    $(element).find("h3 a").after(htmlToInsert);
                });

                if (globalPostId) {
                    htmlToInsert = getConfigHtml(html, globalPostId);
                    $('article.post h1').append(htmlToInsert);
                }

                //  Mark future posts
                futurePosts = response.data.future_posts;
                
                // Loop gennem alle ID'er og sæt en klasse
                $.each(futurePosts, function (index, postId) {
                    $('.isPost[data-post-id="' + postId + '"]').addClass('is-future');
                });

                applyDividers();

                //  on click show popup
                $(".showInfo span[data-post-id]").filter(function () {
                    return $(this).attr("data-post-id") != "-1";
                }).on("click", function (e) {
                    const parentEl = $(this).closest('.showInfo');
                    

                    attachCategoryDropdown(parentEl, !window.editLists, function (cat, postId, remove) {
                        if (cat == null) {
                            //  Edit lists
                            location.href = window.editListsUrl;
                        }

                        if (typeof cat == "string") {
                            if (cat == "edit") {
                                location.href = `/wp-admin/post.php?post=${postId}&action=edit`;
                            }
                        }

                        const flexBox = $(parentEl).closest('.jsFlexBox .td_block_inner');
                        const categoryFrom = $(flexBox).find(".postInfo").attr("data-category-slug");

                        console.log('Flyt post til kategori:', postId, cat.slug, remove);

                        targetCat = 'alle-nyheder';
                        if (!remove) {
                            targetCat = cat.slug;
                        }
                        
                        $.post(myAjax.ajaxurl, {
                            action: 'move_to_new_category',
                            post_id: postId,
                            targetCategory: targetCat,
                            nonce: myAjax.nonce
                        }, function (response) {
                            if (response.success) {
                                if (!globalPostId) {
                                    reloadSection(flexBox, function () {
                                        addConfigurationOptions();
                                    });

                                    $('.jsFlexBox').each(function () {
                                        flexBoxCat = $(this).attr("data-category-slug");
                                        if (flexBoxCat == targetCat) {
                                            reloadSection($(this).find(".td_block_inner"), function () {
                                                addConfigurationOptions();
                                            });
                                        }
                                    });
                                }
                                else {
                                    addConfigurationOptions();
                                }
                            } else {
                                alert('Fejl: ' + (response.data?.message || 'Ukendt fejl'));
                            }
                        });

                    });
                });
            } else {
                alert('Fejl: ' + (response.data?.message || 'Ukendt fejl'));
            }
        });
    }

    function getConfigHtml(html, postId, category) {
        let htmlToInsert = html.replace("{id}", postId);
        htmlToInsert = htmlToInsert.replace("{slug}", category);
        let alertTxt = "";

        if (postId == -1) {
            alertTxt = " onclick=\"alert('Kan ikke finde postId. PostId´et findes på baggrund af ´Edit´ knap på billedet på elementet, så hvis det ikke vises (må godt være hidden) kan postId ikke findes og så virker skidtet ikke ;-0\');return false;\"";
        }

        htmlToInsert = htmlToInsert.replace("{alert}", alertTxt);
        return htmlToInsert;
    }

    function getPostId(postElement) {
        let href = jQuery(postElement).find("a.td-admin-edit").attr("href");
        if (href === undefined) {
            return -1;
        }

        let url = new URL(href);
        const postId = url.searchParams.get('post');
        return postId;
    }

    function reloadSection(flexBox, callback) {
        const $list = $(flexBox);
        console.log(flexBox, $(flexBox).attr('id'));

        // Tilføj en visuel loader
        $list.addClass('is-loading');


        const flexId = $(flexBox).attr('id');
        // Hent nyt indhold (kun indholdet af boksen)
        $.get(window.location.href, function (data) {
            const $newContent = $(data).find('#' + flexId + '> *');

            // Sammenlign gammel og ny HTML
            const oldHtml = $list.html().trim();
            const newHtml = $newContent.html().trim();

            if (oldHtml !== newHtml) {
                // Kun opdater hvis der er forskel
                $list.html($newContent);
                console.log('🔄 Indhold opdateret (ændringer fundet)');
                // Trigger evt. resize og scroll, hvis layout skal genberegnes
                $($list).trigger('resize');
                $($list).trigger('scroll');

                // Evt. callback
                if (typeof callback === 'function') callback();
            } else {
                console.log('✅ Indhold uændret – ingen opdatering');
            }

            $list.removeClass('is-loading');
        });
    }

    // Callback-funktion
    function postMoved($fromParent, $toParent, $item) {
        var toSlug = $($toParent).closest('.jsFlexBox').attr("data-category-slug");

        const toIds = $(`.jsFlexBox[data-category-slug="${toSlug}"] .td-cpt-post`)
            .map(function () {
                return $(this).data('post-id');
            })
            .get(); // <- konverterer jQuery-objektet til et rent JS-array


        $.post(myAjax.ajaxurl, {
            action: 'set_category_list',
            list: toIds,
            targetCategory: toSlug,
            nonce: myAjax.nonce
        }, function (response) {
            if (response.success) {
                applyDividers();
            } else {
                alert('Fejl: ' + (response.data?.message || 'Ukendt fejl'));
            }
        });
    }

    function applyDividers() {
        if (!window.editLists) {
            return;
        }

        frontpageBoxes.forEach(function (item, index) {
            applyDivider(".jsFlexBox[data-category-slug='" + item.category.slug + "']", item.limit);
        });
    }

    function applyDivider(parentSelector, limit) {
        // Fjern tidligere markeringer
        $(parentSelector + ' .isPost').removeClass('has-divider');

        let count = 0;
        console.log($(parentSelector + ' .isPost'));

        $(parentSelector + ' .isPost').each(function () {
            const $post = $(this);

            // Spring future-indlæg over
            if ($post.hasClass('is-future')) {
                return; // continue
            }

            count++;
            $post.removeClass('not-visible');

            if (count === limit) {
                $post.addClass('has-divider');
            }
            else if (count > limit) {
                $post.addClass('not-visible');
            }
        });
    }
});

