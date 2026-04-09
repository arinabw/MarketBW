# История проекта MarketBW

Журнал итераций для восстановления контекста агентами (правило **project-history**). Ведите записи после завершённых задач.

## Унификация с шаблоном agentrules

### Приведение к общим правилам Cursor и GRACE в grace/
- **Что:** заменены правила **`.cursor/rules/`** на семь унифицированных файлов из корня репозитория `agentrules` (`agents-grace`, `grace-artifact-sync`, `git-version-commit`, `project-history`, `read-history-on-start`, `dev-environment`, `no-local-app-verification`); XML GRACE перенесены из **`docs/*.xml`** в **`grace/requirements/requirements.xml`**, **`grace/technology/technology.xml`**, **`grace/plan/development-plan.xml`**, **`grace/verification/verification-plan.xml`**, **`grace/knowledge-graph/knowledge-graph.xml`**; добавлены **`grace/README.md`**, **`docs/AGENTS.md`** (полная карта проекта), **`docs/HISTORY.md`**; корневой **`AGENTS.md`** оставлен как отсылка к **`docs/AGENTS.md`**. В **`grace/plan/development-plan.xml`** обновлены `shared-artifacts` и описание фазы GRACE.
- **Почему:** запрос пользователя — унификация **MarketBW** с эталоном **DitPortal** / **LLMTester** и политикой «данные GRACE только в **`grace/**`**».
- **Файлы:** `.cursor/rules/*.mdc`, `docs/AGENTS.md`, `docs/HISTORY.md`, `AGENTS.md`, `grace/**` (перенос из бывших `docs/*.xml`).
