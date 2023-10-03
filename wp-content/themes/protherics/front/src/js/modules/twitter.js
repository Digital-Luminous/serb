const selector = {
  twitterBox: '.js-twitter',
};

const Twitter = {
  container: null,
  apiURL: '',
  init: () => {
    Twitter.container = document.querySelector(selector.twitterBox);
    Twitter.apiURL = Twitter.container?.dataset?.url;

    const shouldContinue = !!Twitter.container && !!Twitter.apiURL.length;

    if (!shouldContinue) return;

    Twitter.fetchData();
  },
  fetchData: async () => {
    try {
      const response = await fetch(Twitter.apiURL);
      const posts = await response.json();

      if (!posts.length) return;

      const latestPost = posts[0];
      const renderedPost = Twitter.renderNews(latestPost);

      Twitter.container.insertAdjacentHTML('afterbegin', renderedPost);
    } catch (error) {
      console.error(error);
    }
  },
  renderNews: latestPost => {
    const { id, id_str, user, created_at, full_text } = latestPost;
    const { screen_name: userName } = user;
    const creationDate = new Date(created_at);
    const creationDay = creationDate.getDate();
    const shortCreationMonth = creationDate.toLocaleString('default', { month: 'short' });
    const targetUserUrl = `https://twitter.com/${userName}`;
    const targetPostUrl = `https://twitter.com/${userName}/status/${id_str}`;

    return `
      <div class="c-news-box c-news-box--is-twitter">
        <div class="c-news-box__user-data">
          <div class="c-news-box__column">
            <div class="c-news-box__author ui-color--white-1 ui-font-weight--semibold">
              <a class="c-news-box__link" href='${targetUserUrl}' target="_blank" rel="nofollow">
                @${userName}
              </a>
            </div>
            <div class="c-news-box__date t-size-14 ui-color--white-1">
              ${creationDay} ${shortCreationMonth}
            </div>
          </div>
          <div class="c-news-box__column">
            <img class="c-news-box__icon" src='${window.location.origin}/wp-content/themes/protherics/front/static/images/icon-twitter.svg' alt="Twitter icon">
          </div>
        </div>
        <div class="c-news-box__text t-size-20 ui-color--white-1">
          ${full_text}
        </div>
        <div class="c-news-box__actions">
          <a class="c-btn c-btn--secondary c-btn--arrowed" target="_blank" rel="nofollow" href='${targetPostUrl}'>
            Read more
          </a>
        </div>
      </div>
    `;
  },
};

Twitter.init();
