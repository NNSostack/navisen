globalPostId = null;

jQuery(function ($) {
    const body = document.body;
    const postIdClass = Array.from(body.classList).find(c => c.startsWith('postid-'));
    globalPostId = postIdClass ? postIdClass.replace('postid-', '') : null;
});