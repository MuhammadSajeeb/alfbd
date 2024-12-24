( function($) {    
    jQuery( document ).ready( function() {
        const isRankMathSupportEnabled = ultpFsSettings?.seo_support==='yes';
        if(wp?.plugins) {
            let plugins = wp.plugins.getPlugins();
            plugins.forEach(plugin => {
                if(isRankMathSupportEnabled && plugin.name=="rank-math") {
                    return;
                }
                if( plugin.name=="ultp-fs-guest-info") {
                    return;
                }
                wp.plugins.unregisterPlugin(plugin.name);
            });
        }
        var scripts = jQuery('script');
        var allowedScripts = ['ultimate-post','gutenberg'];
        var nonAdminScripts = scripts.filter(function () {
            var src = jQuery(this).attr('src');
            if(src ) {
                if( (src.includes('/wp-content/plugins')  ) ) {
                    allowedScripts.forEach((as=>{
                        if(!src.includes('/wp-content/plugins/'+as) ) {
                            return true;
                        }
                    }))
                } else {
                    return false;
                }
            } else {
                return false;
            }
        });

        let len = nonAdminScripts.length;
        for(let i = 0; i<len; i++) {
            nonAdminScripts[i].remove();
        }
        if(wp?.data) {
            wp.data.dispatch('core/edit-post').removeEditorPanel('discussion-panel');
            wp.data.dispatch('core/rich-text').removeFormatTypes('ultimate-post/chatgpt');
        }
        if(!ultpFsSettings?.media_access || 'no'==ultpFsSettings?.media_access) {
            wp.data.dispatch('core/edit-post').removeEditorPanel('featured-image');
        }

        setTimeout(()=>{
            $('.toolbar-insert-layout').remove();
            const toolbar = document.getElementsByClassName('edit-post-header-toolbar')[0];
            const children = toolbar?.children;

            if(children) {
                for (let i = 1; i < children.length; i++) {
                toolbar.removeChild(children[i]);
                }
            }
        },[1000]);

        if(wp?.data) {
            const { subscribe,select } = wp.data;
            const { isSavingPost } = select( 'core/editor' );
            var checked = true;
            subscribe( () => {
                if ( isSavingPost() ) {
                    checked = false;
                } else {
                    if ( ! checked ) {
                        checkNotificationAfterPublish();
                        checked = true;
                    }
    
                }
            } );
    
            function checkNotificationAfterPublish(){
                const postId = wp.data.select("core/editor").getCurrentPostId();
                const url = wp.url.addQueryArgs(
                    '/wp-json/ultp/v2/fs_get_editor_notice',
                    { id: postId },
                );
                wp.apiFetch({
                    url,
                }).then(
                    function(response){
                        if(response.message){
                            wp.data.dispatch("core/notices").createNotice(
                                response.code,
                                response.message,
                                {
                                    id: 'email_status_notice',
                                    isDismissible: true
                                }
                            );
                        }
                    }
                );
            };
        }

    } );
} )(jQuery);

