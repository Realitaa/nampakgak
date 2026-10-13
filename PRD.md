# Product Requirements Document (PRD)

## NampakGak? --- Campus Lost and Found

**Document status:** Initial product scope\
**Product type:** Web application\
**Primary audience:** Students and other members of the campus
community\
**Initial scope:** Universitas Negeri Medan (UNIMED), with an initial
focus on FMIPA

## 1. Product Overview

NampakGak? is a centralized lost-and-found website for the campus
community. It helps people report lost belongings, publish information
about items they have found, search existing reports, and coordinate the
return of belongings to their rightful owners.

The product aims to replace scattered announcements in chat groups,
verbal reports, and physical notice boards with a searchable, organized
place for lost-and-found information.

## 2. Problem Statement

The current approach to reporting lost and found items has several
limitations:

- **Scattered information:** Reports are spread across different chat
  groups and other channels.
- **Short-lived visibility:** Messages can quickly become buried under
  newer conversations.
- **Difficult discovery:** People lack a consistent way to search
  reports by item type, location, or date.
- **Risk of false claims:** Publicly sharing every identifying detail
  can make it easier for someone to claim an item that is not theirs.
- **Unclear outcomes:** Reporters may not know whether an item is
  still missing, awaiting a claim, or has been returned.

NampakGak? addresses these problems through a shared reporting, search,
and claim process.

## 3. Product Goals

The product should:

1.  Provide one accessible place for campus lost-and-found reports.
2.  Make it easy for users to report lost or found belongings.
3.  Help users discover relevant reports through keyword search and
    filters.
4.  Support a claim process that asks claimants to provide evidence of
    ownership.
5.  Make the status of reports and claims clear.
6.  Give administrators tools to maintain an orderly and appropriate
    platform.

## 4. Target Users

### 4.1 Campus Community Members

Students and other campus community members who have lost or found an
item. They can create reports, browse and search reports, submit
ownership claims for found items, and manage their own reports and
claims.

### 4.2 Administrators

Designated administrators who oversee the platform. They can moderate
inappropriate or spam reports, maintain item categories and campus
locations, review platform activity, and help address claim disputes.

## 5. Scope and Key Features

### 5.1 Account Access

- Users can create an account, sign in, and sign out.
- The platform distinguishes regular users from administrators.
- Users can access features according to their role.

### 5.2 Lost Item Reports

- Users can report an item they have lost.
- A report can include a title, category, description, approximate
  location, date or time, and an optional photo.
- Users can review and manage their own reports.
- Users can update a report's status when the situation changes.

### 5.3 Found Item Reports

- Users can report an item they have found.
- A report can include a title, category, place and date of discovery,
  a general description, and a photo.
- Users should be able to avoid revealing every identifying detail
  publicly, helping preserve information that can be used to verify
  ownership.
- Users can review and manage their own found-item reports.

### 5.4 Browse, Search, and Filter

- Users can browse recent lost and found reports.
- Users can search reports by keywords.
- Users can filter reports by report type, category, campus location,
  and date.
- Each report provides a detail view with the information appropriate
  for public display.

### 5.5 Ownership Claims

- A user can submit a claim for a found item they believe belongs to
  them.
- A claim should include a description of ownership evidence and may
  include supporting information or an image.
- The person who reported finding the item can review the claim.
- The finder can approve or reject a claim.
- A rejected claim can include a reason.
- The platform records the claim outcome and updates the item's status
  when a claim is approved.

### 5.6 Personal Dashboard

Users can access an area to review their own lost and found reports and
claims, including their current statuses and relevant claim updates.

### 5.7 Administration and Moderation

Administrators can:

- View and monitor reports across the platform.
- Remove spam or inappropriate reports.
- Manage item categories.
- Manage campus locations.
- Help mediate disputed claims.

## 6. Main User Journeys

### Journey A: Reporting a Lost Item

1.  A user signs in.
2.  The user creates a lost-item report and provides the available
    details.
3.  The report is published.
4.  Other users can discover it through browsing, search, or filters.
5.  The reporter can update the report when its status changes.

### Journey B: Reporting a Found Item

1.  A user signs in.
2.  The user creates a found-item report with the item's details and
    discovery location.
3.  The report is published without unnecessarily exposing sensitive
    identifying details.
4.  Other users can discover the report and review its details.

### Journey C: Claiming a Found Item

1.  A user finds a report for an item they believe is theirs.
2.  The user submits a claim with evidence of ownership.
3.  The finder reviews the submitted evidence.
4.  The finder approves or rejects the claim.
5.  The claim outcome is recorded and the item's status is updated when
    appropriate.
6.  The parties can coordinate the return of the item.

### Journey D: Moderating the Platform

1.  An administrator reviews reports and platform activity.
2.  The administrator removes spam or inappropriate content when
    necessary.
3.  The administrator maintains categories and campus locations.
4.  The administrator helps address disputed claims when needed.

## 7. Product Rules and Statuses

- Users should be able to manage their own reports; they should not be
  able to change or delete other users' reports unless they have
  administrative permission.
- A user should not be able to approve their own claim simply by being
  the claimant.
- Ownership claims should be assessed using evidence that is not fully
  disclosed in the public report.
- An approved claim should result in the item being marked as returned
  or resolved.
- Rejected claims should retain their outcome and any rejection
  reason.
- Administrative actions should be limited to designated
  administrators.

Suggested user-facing item statuses:

- **Active:** The report is still relevant and unresolved.
- **Claim in progress:** A claim is being reviewed.
- **Resolved / Returned:** The item is no longer awaiting a match or
  has been returned, as applicable.

Suggested claim statuses:

- **Pending:** Awaiting review.
- **Approved:** The ownership claim has been accepted.
- **Rejected:** The ownership claim has been declined.

## 8. Non-Functional Product Expectations

- **Usability:** The reporting and claim flows should be
  understandable to users with minimal guidance.
- **Responsive experience:** The website should be usable on desktop
  and mobile screens.
- **Privacy:** Public reports should not expose unnecessary personal
  information or all identifying details of found items.
- **Security:** Account access, report management, and claim decisions
  should be protected against unauthorized actions.
- **Data integrity:** Report and claim statuses should remain
  consistent as users take actions.
- **Accessibility:** Text, controls, and navigation should remain
  clear and usable.

## 9. MVP Priorities

The first release should focus on the core lost-and-found workflow.

### Must Have

- Account registration and sign-in.
- Create and manage lost-item reports.
- Create and manage found-item reports.
- Browse, search, and filter reports.
- View report details.
- Submit ownership claims with evidence.
- Review, approve, or reject claims.
- Track report and claim statuses.
- Basic administrator moderation.
- Manage categories and campus locations.

### Out of Scope for the Initial Release

The following are potential future enhancements and are not required for
the initial release:

- Automated WhatsApp or Telegram notifications.
- Interactive maps or precise geolocation.
- Automated matching of lost reports with found reports.
- Advanced analytics and reporting.
- Native mobile applications.

## 10. Success Criteria

The MVP can be considered functionally complete when:

1.  A user can register and sign in.
2.  A user can publish and manage a lost-item report.
3.  A user can publish and manage a found-item report.
4.  Users can find reports using search and filters.
5.  A user can submit an ownership claim with evidence.
6.  The finder can approve or reject the claim.
7.  The system records the claim outcome and reflects the appropriate
    item status.
8.  Users can review their own reports and claims.
9.  An administrator can remove inappropriate reports and manage
    categories and locations.
10. Unauthorized users cannot perform actions reserved for report owners
    or administrators.

## 11. Assumptions and Open Questions

The following points should be confirmed by the project team before or
during implementation:

- Whether registration is open to any email address or restricted to
  campus email addresses.
- Whether contact details are shown publicly or only shared when
  necessary.
- Whether a found-item report may receive multiple simultaneous
  pending claims.
- Whether a claim can be withdrawn or reconsidered after rejection.
- What exact status transitions are permitted for lost and found
  reports.
- Whether administrators can directly resolve claims or only mediate
  disputes.
- How and where the physical handover of an item is arranged.

## 12. Product Summary

NampakGak? is intended to make campus lost-and-found information easier
to publish, discover, and act on. Its central value is not merely
listing lost and found items, but providing a structured process that
connects reports to ownership claims while making statuses clear and
reducing opportunities for false claims.
