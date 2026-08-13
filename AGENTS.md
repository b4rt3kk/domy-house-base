# Repository instructions

## MCP and model efficiency

- At the start of a task, check whether an available MCP server can perform the requested external operation directly. Prefer that MCP server when it can perform the exact operation safely; otherwise use the appropriate local or supported method.
- For direct database operations, use the applicable available database MCP server first. Use another supported method only when MCP is unavailable or cannot perform the exact operation.
- For long-running database copy, dump, restore, or comparison operations, start the job and wait for its completion without continuous status polling. Do not make the first status check before about ten minutes unless an error, timeout, cancellation, or a user request requires it.
- Before executing a simple, mostly mechanical MCP task (for example a Git push, GitHub deployment, database backup, restore, or comparison) on a high-cost model, suggest `gpt-5.6-luna` with low reasoning effort, if available, and wait for the user's explicit decision to switch or continue on the current model.
- Before beginning a complex task on a low-cost model, suggest `gpt-5.6-sol` with at least medium reasoning effort, if available, and wait for the user's explicit decision to switch or continue on the current model.

## Source of truth

- Git remote `origin` is the sole source of truth for code and branches. Publish code changes only to `origin` by default, using the applicable available MCP server first; use another supported method only when MCP is unavailable or cannot perform the exact operation.
- The `gitea` remote is a backup code repository. Do not fetch, compare, push, or synchronize code with it unless the user explicitly requests that exact Gitea action. Gitea remains the authoritative issue tracker: read, create, and update issues there, using the available Gitea MCP server first, and only with the required user approval.
- Before editing, inspect the current branch, worktree, relevant implementation, and focused tests. Preserve unrelated changes.
- Do not commit, push, merge, deploy, create/update issues, or modify issue state without explicit approval for the exact repository, remote, branch, commit, and external action.
- Run focused validation and `git diff --check` after changes.

## Task branch workflow

- Never run `git clone` or `git worktree add` merely to start, continue, or isolate a task. Work in the user-selected existing repository; creating another checkout or worktree requires explicit user approval.
- This repository uses only `master` as its base and integration branch; do not create or expect `develop` or `release/*` branches. Unless the user explicitly names a different base, start every new task by switching the current existing checkout to local `master` and aligning it with `origin/master`.
- Before switching branches, inspect the current branch and worktree. If there are staged, unstaged, or untracked changes, do not commit, push, stash, discard, or carry them onto another branch automatically. Summarize their purpose and scope from `git status`, a diff summary, and only the focused diff needed to explain them. Then present a numbered prompt: (1) finish them on the current branch, with separate commit/push approval; (2) stash them, including untracked files, under a descriptive label; (3) continue the new task on the current branch as an explicit exception; or (4) provide another instruction as free text. Wait for the user's choice.
- After the worktree is resolved, run `git fetch origin`, verify that `origin/master` exists, switch to local `master`, and update it only by fast-forward (`git pull --ff-only origin master`). If `master` is ahead or divergent, stop and ask the user how to establish the base; never merge, rebase, reset, or force-update it by assumption. Confirm local `master` and `origin/master` have the same SHA before branching.
- Use the repository's issue-branch convention for issue work. Without an issue, create `task/<short-kebab-case-description>` from `master`; if that name exists, append the lowest available numeric suffix. Never use `master/...`, because it conflicts with the existing `master` ref.
- After the user accepts the implementation, the work must be integrated into `master` through the GitHub workflow. If the acceptance does not explicitly authorize the exact push, pull request, and merge, request that approval first. Never commit directly to `master`, and do not deploy automatically.
