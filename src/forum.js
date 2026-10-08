const live = document.querySelector('.social-live');
let liveTimer;
function announce(message) {
  live.textContent = message;
  live.classList.add('is-visible');
  clearTimeout(liveTimer);
  liveTimer = setTimeout(() => live.classList.remove('is-visible'), 2400);
}
function appendComment(data, form) {
  const template = document.createElement('template');
  template.innerHTML = data.html;
  const panel = form.closest('.social-comments');
  const target = data.parentId
    ? panel.querySelector(`#comment-${data.parentId} > .social-replies`)
    : panel.querySelector('[data-comment-list], #comment-list');
  target?.append(template.content);
}
document.addEventListener('submit', async event => {
  const form = event.target.closest('form[data-async-action]');
  if (!form) return;
  event.preventDefault();
  const button = form.querySelector('[type=submit]');
  if (button.disabled) return;
  button.disabled = true;
  try {
    const response = await fetch(location.href, { method: 'POST', headers: { 'X-Requested-With': 'fetch' }, body: new FormData(form) });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Action failed.');
    const action = form.dataset.asyncAction;
    const card = form.closest('.social-post') || document.querySelector('.social-post');
    if (action === 'follow') {
      document.querySelectorAll(`.social-follow[data-author-id="${data.authorId}"]`).forEach(follow => {
        follow.setAttribute('aria-pressed', String(data.active));
        follow.textContent = data.active ? 'Following' : '+ Follow';
      });
      announce(data.active ? 'Following this member' : 'Unfollowed');
    } else if (action === 'like' || action === 'save') {
      const pressed = form.querySelector('button');
      pressed.setAttribute('aria-pressed', String(data.active));
      pressed.textContent = action === 'like' ? (data.active ? '♥ Liked' : '♡ Like') : (data.active ? '✓ Saved' : '◇ Save');
      if (action === 'like') card.querySelector('[data-like-count]').textContent = `${data.likes} likes`;
      announce(action === 'like' ? (data.active ? 'Liked' : 'Like removed') : (data.active ? 'Saved' : 'Removed from saved'));
    } else if (action === 'comment_like') {
      const thread = form.closest('.social-comment-thread');
      button.setAttribute('aria-pressed', String(data.active));
      button.textContent = data.active ? '♥ Liked' : '♡ Like';
      thread.querySelector('[data-comment-likes]').textContent = `${data.likes} likes`;
      announce(data.active ? 'Comment liked' : 'Comment like removed');
    } else if (action === 'comment') {
      appendComment(data, form);
      form.reset();
      closeComposer(form);
      const count = form.closest('.social-comments').querySelector('[data-comments-total]');
      if (count) count.textContent = data.comments;
      const link = card.querySelector('[data-comment-count]');
      if (link) link.textContent = `${data.comments} comments`;
      announce('Comment posted');
    }
  } catch (error) { announce(error.message || 'Something went wrong.'); }
  finally { button.disabled = false; }
});
function openComposer(form) {
  form.hidden = false;
  requestAnimationFrame(() => form.classList.add('is-open'));
  form.querySelector('textarea')?.focus({ preventScroll: true });
}
function closeComposer(form) {
  form.classList.remove('is-open');
  window.setTimeout(() => { if (!form.classList.contains('is-open')) form.hidden = true; }, 190);
}
document.addEventListener('click', async event => {
  const commentsButton = event.target.closest('[data-show-comments]');
  if (commentsButton) {
    const card = commentsButton.closest('.social-post');
    const slot = card.querySelector('[data-comments-slot]');
    if (!slot) { document.querySelector('#comments')?.scrollIntoView({ block: 'nearest' }); return; }
    if (!slot.dataset.loaded) {
      commentsButton.disabled = true;
      try {
        const response = await fetch(`forum?comments=${commentsButton.dataset.showComments}`, { headers: { 'X-Requested-With': 'fetch' } });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Could not load comments.');
        slot.innerHTML = data.html;
        slot.dataset.loaded = 'true';
      } catch (error) { announce(error.message || 'Could not load comments.'); commentsButton.disabled = false; return; }
      commentsButton.disabled = false;
    }
    slot.hidden = !slot.hidden;
    card.querySelectorAll('[data-show-comments]').forEach(button => button.setAttribute('aria-expanded', String(!slot.hidden)));
    return;
  }
  const compose = event.target.closest('[data-compose-toggle]');
  if (compose) {
    const form = compose.nextElementSibling;
    if (form.classList.contains('is-open')) closeComposer(form);
    else openComposer(form);
    return;
  }
  const reply = event.target.closest('[data-reply-to]');
  if (reply) {
    const form = reply.closest('.social-comment-thread').querySelector(':scope > .social-reply-form');
    if (form.classList.contains('is-open')) closeComposer(form);
    else openComposer(form);
    return;
  }
  const button = event.target.closest('[data-share-url]');
  if (!button) return;
  const url = new URL(button.dataset.shareUrl, location.href).href;
  try { await navigator.clipboard.writeText(url); announce('Link copied'); }
  catch (_) { announce('Could not copy the link'); }
});
