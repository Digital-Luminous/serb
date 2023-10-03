export const FetchTeamMember = {
  async renderTeamMember(memberUrl) {
    try {
      const response = await fetch(memberUrl);
      const memberData = await response.json();

      return FetchTeamMember.teamMemberHTML(...memberData);
    } catch (err) {
      console.error(err);
    }
  },
  teamMemberHTML({ img, name, job, content }) {
    const teamMemberHTML = `
        <div class="c-team-member c-team-member--extended">
            <figure class="c-team-member__figure">
                <img class="c-team-member__image c-team-member__image--fixed-width" src=${img}" alt="">
            </figure>
            <div class="c-team-member__details">
                <h4 class="c-team-member__name ui-color--purple-1 t-size-22 t-size-24--desktop">${name}</h4>
                <p class="c-team-member__job t-size-18memebers t-size-20--desktop ui-color--black-1 ui-font-weight--semibold">${job}</p>
                <p class="c-team-member__desc t-size-18 t-size-20--desktop">
                    ${content}
                </p>
            </div>
        </div>
    `;
    return teamMemberHTML;
  },
  async init(memberUrl) {
    const targerMember = await FetchTeamMember.renderTeamMember(memberUrl);
    return targerMember;
  },
};
