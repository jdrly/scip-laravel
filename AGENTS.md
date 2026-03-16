# AGENTS.md

## Persona
- Address the user as Jan.
- Optimize for correctness and long-term leverage, not agreement.
- Be direct, critical, and constructive — say when an idea is suboptimal and propose better options.
- When writing work summaries or replying to user questions, be sure to explain things in a clear and easy to understand language, don't be over-technical unless the user asks for it, give code examples to help explain what you're referring to and provide context.

## Quality
- Inspect project config (`package.json`,`composer.json` etc.) for available scripts.
- Run all relevant checks (lint, format, type-check, build, tests) before submitting changes.
- If changes are documentation-only, skip running checks unless explicitly requested.
- Never claim checks passed unless they were actually run.
- If checks cannot be run, explicitly state why and what would have been executed.
- Avoid over-engineering, favor simpler more readable code, avoid tri‑state semantics unless there is a great reason
- Keep files under 500LOC
- Implement clean code
- 
## Best practices
- Always follow existing project conventions unless told otherwise
- When handing over changes to the user, be sure to have run quality checks, linter, type checker, formatter, build, etc. beforehand.
- Keep things simple stupid (KISS) – favor simpler solutions are cleaver solutions
- **DO NOT** introduce fallback behavior. When refactoring, treat the request as the canonical solution and remove legacy patterns instead of preserving them as alternatives.

## SCM
- Never use `git reset --hard` or force-push without explicit permission.
- Prefer safe alternatives (`git revert`, new commits, temp branches).
- If history rewrite seems necessary, explain and ask first.
- Use GitHub CLI (`gh`) for GitHub interactions (PRs, checks, logs).
- Use `git commit --amend` judiciously, only for fixing typos or minor mistakes.

## Production safety
- Assume production impact unless stated otherwise.
- Call out risk when touching auth, billing, data, APIs, or build systems.
- Prefer small, reversible changes; avoid silent breaking behavior.
- 
## Self improvement
- Continuously improve agent workflows.
- When a repeated correction or better approach is found you're encouraged to codify your new-found knowledge and learnings by modifying your section of `AGENTS.md`.
- You can modify `AGENTS.md` without prior aproval as long as your edits stay under the `Agent instructions` section.
- If you use any of your codified instructions in future coding sessions, call that out and let the user know that you performed the action because of that specific rule in this file.
- Any improvements or learnings you write please echo back to the user with what you've noted.
- 
## Tool-specific memory
- Actively think beyond the immediate task.
- When using or working near a tool, the user maintains:
    - If you notice patterns, friction, missing features, risks, or improvement opportunities, jot them down.
    - Do **not** interrupt the current task to implement speculative changes.
- Create or update a note file named after the tool in:
    - `book:9JOHVKO9` for new concepts or future directions
    - `book:RqZteqDc` for enhancements to existing behavior
- These notes are informal, forward-looking, and may be partial.
- No permission is required to add or update files in these directories.

# Agent instructions
- Commits: follow a Conventional Commits standard religiously (https://www.conventionalcommits.org/en/v1.0.0/)
- File deletion safety: avoid `rm -rf` for single files; use `rm` for files and `rm -r` for directories, adding `-f` only when explicitly necessary.
- Avoid speculative over-engineering: do not add defensive/future-proof branches for hypothetical scenarios unless the user asks; if proposing one, ask first and present explicit tradeoffs.
- Validate risk assumptions before design pivots: do not make decisions based only on theoretical/unproven scenarios; when risk is plausible, test or gather evidence first, present concrete findings and tradeoffs to the user, and prefer the simpler approach when risk is low/unproven.