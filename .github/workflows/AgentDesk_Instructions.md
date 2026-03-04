AgentDesk Development Roadmap & AI Context

You are an Expert Laravel 12 & Livewire Engineer. You are building "AgentDesk", a modern, lightweight helpdesk augmented with AI triage and reply assistance. This project is a strict 15-day evaluation build, meaning code quality, architectural purity, and strict adherence to the defined stack are non-negotiable.

🏗️ GLOBAL RULES & ARCHITECTURE (STRICT ADHERENCE REQUIRED)

Tech Stack & Baseline:

PHP 8.4+, Laravel 12+, Livewire 3, TailwindCSS v3/4.

Laravel AI SDK: You must strictly use the Groq provider. Do not default to OpenAI or Anthropic.

Base Kit: Assume nunomaduro/laravel-starter-kit is the foundation.

Strict Typing & Modern PHP 8.4+:

Every single PHP file MUST begin with declare(strict_types=1);.

Heavily utilize PHP 8.4 features: constructor property promotion, readonly classes/properties, typed arrays (via PHPDoc), and strict return types.

Data Transfer Objects (DTOs): Use readonly DTOs for all data crossing boundaries (e.g., from a Controller to an Action, or from an AI Tool to an AI Agent). Never pass raw associative arrays between application layers.

No Logic in Models (Action/Service Pattern):

Controllers & Livewire Components should be exceptionally thin (1-3 lines). Their only job is to gather input, validate it (using Form Requests or Livewire Form Objects), and dispatch it.

Actions/Services: All business logic lives here (e.g., CreateTicketAction, RunTriageAction). Actions should have a single public execute() or handle() method.

Models: Eloquent models are STRICTLY for defining relationships (belongsTo, hasMany), casts, and local query scopes. No business logic, no complex mutators.

Terminology Clarity:

SupportAgent: A Human user (role: Agent) who logs into the dashboard to review, assign, and resolve tickets.

AI Agent: A PHP class (e.g., TriageAgent) running Laravel AI SDK logic inside a Queued Job. AI Agents never perform direct UI actions; they draft structured outputs for the Human SupportAgent to review and approve.

Quality Gates & CI/CD:

Code MUST pass composer lint (running Pint and Rector for standardized formatting).

Code MUST pass composer test:types (running PHPStan at a strict level, usually Level 9). You must fix all mixed types and iterable type hints.

Code MUST pass composer test (Pest PHP). Test coverage must account for edge cases, and type coverage must be 100%.

AI Constraints & Rate Limiting (Groq Free Tier):

LLM calls use the Groq Free API, which has aggressive rate limits.

Caching: You MUST implement input_hash caching. Hash the ticket content; if the hash hasn't changed, do not run the AI again.

Asynchronous Execution: AI Agents ONLY run in Queued Jobs, never synchronously during a web request. This prevents the UI from hanging while waiting for the LLM.

🚀 STEP-BY-STEP WORKFLOW & PROMPTS

Wait for the developer to specify which phase to execute in the chat. When a phase is requested, execute ALL the requirements listed under that phase, prioritizing the architectural intents described below.

Phase 1: Domain Modeling & Database

Trigger Prompt: "Execute Phase 1: Domain Modeling & Database"

Architectural Intent: Build a bulletproof foundation. Use soft deletes where appropriate, and ensure database constraints (foreign keys, strict string lengths) match the domain rules.

Enums: Create backed enums (string or int backed) for:

TicketStatus (New, Triaged, InProgress, Waiting, Resolved)

TicketPriority (Low, Medium, High, Urgent)

TicketMessageType (Public, Internal)

AiRunType (Triage, ReplyDraft)

AiRunStatus (Queued, Running, Succeeded, Failed)

UserRole (Admin, Agent, Requester).

Migrations: Generate strict migrations with constrained()->cascadeOnDelete() where appropriate for: users (add role enum column), categories, tickets, ticket_messages, attachments (polymorphic or tied to tickets/messages), ai_runs (must include string input_hash, json output_json, text error_message), kb_articles, settings (for global SLA configs like first_response_hours).

Models: Generate strictly typed Models with exact $casts mapping to the Enums created above.

Phase 2: Core Logic & Authorization

Trigger Prompt: "Execute Phase 2: Core Logic & Authorization"

Architectural Intent: Secure the perimeter. No user should be able to access or manipulate data they do not own or have rights to.

Auth & Policies: Implement Laravel Policies or Gates.

Requesters can only view/update tickets where user_id === auth()->id().

SupportAgents can view tickets where assignee_id === auth()->id() OR status === TicketStatus::New (the triage queue).

Admins bypass all checks.

Actions: Create dedicated Action classes:

CreateTicketAction(TicketDTO $data): Ticket

AddTicketMessageAction(MessageDTO $data): TicketMessage

AssignTicketAction(Ticket $ticket, User $agent): void

Attachments Security: Implement secure, private local storage (do not store in the public disk). Create a controller (AttachmentDownloadController) that uses signed routes or explicitly calls the AttachmentPolicy to ensure unauthorized users cannot download files.

Phase 3: AI Infrastructure & Jobs

Trigger Prompt: "Execute Phase 3: AI Infrastructure & Jobs"

Architectural Intent: Isolate the LLM interactions to prevent system failures if the Groq API goes down or rate limits are hit.

AI SDK Setup: Ensure the Laravel AI SDK configuration strictly points to the Groq provider.

Base AI Job: Create an abstract AiAgentJob class that implements ShouldQueue. It must handle:

Updating the corresponding ai_runs record status to AiRunStatus::Running.

Wrapping the AI SDK call in a robust try/catch block.

Handling rate limit exceptions with $this->release(60) (exponential backoff).

Saving the successful structured output to the output_json column and setting status to Succeeded, OR setting status to Failed and populating error_message.

Rate Limiting: Apply Illuminate\Support\Facades\RateLimiter within the job dispatcher (e.g., limit triggers to 5 runs per ticket per hour).

Phase 4: AI Agents & Tools (The Core)

Trigger Prompt: "Execute Phase 4: AI Agents & Tools"

Architectural Intent: Ensure the AI returns predictable, machine-readable data (JSON) rather than conversational text, so the application can programmatically apply the results.

DTOs: Create strictly typed readonly classes for AI inputs/outputs: TriageInput, TriageResult, ReplyDraftInput, ReplyDraftResult, and KbSnippetDTO.

AI Tool (SearchKnowledgeBaseTool): Build a tool class compatible with the Laravel AI SDK. It should accept a $query string, search the kb_articles table (using DB LIKE or basic full-text search), and return an array of KbSnippetDTO objects.

TriageAgent: Create the code-defined AI Agent. Write a strict system prompt instructing it to analyze the ticket body, categorize it, and output a JSON schema mapping exactly to the TriageResult DTO (Category, Priority, Tags, Escalation Flag).

ReplyDraftAgent: Create an AI Agent that requires the SearchKnowledgeBaseTool. It must use the tool to retrieve context before drafting a professional, empathetic reply. The output must map to ReplyDraftResult.

Phase 5: Livewire UI - Requester & Admin

Trigger Prompt: "Execute Phase 5: Livewire UI - Requester & Admin"

Architectural Intent: Build fast, reactive interfaces without writing custom JavaScript. Rely entirely on Livewire and Tailwind utility classes for layout, responsiveness, and state management.

Requester Portal:

Build TicketCreateForm utilizing Livewire Form Objects (Livewire\Form) for clean validation, including secure file upload handling via WithFileUploads.

Build MyTicketsTable with basic pagination and status filtering.

Admin Settings: Build simple CRUD Livewire components for Categories and Macros. Create a settings page allowing Admins to update the global Response and Resolution SLA targets (saved to the settings table or a local JSON config).

Phase 6: Livewire UI - Support Agent & AI Panel

Trigger Prompt: "Execute Phase 6: Livewire UI - Support Agent & AI Panel"

Architectural Intent: Create a seamless, single-page-app feel for the Support Agents so they can triage and respond rapidly.

Triage Queue: A Livewire table showing 'New', unassigned tickets. Include bulk actions if possible.

Ticket Detail & Thread: Implement the conversation UI. Visually distinguish between Public messages (visible to requesters) and Internal notes (yellow/gray backgrounds, strictly for Agents).

AI Panel (The "Magic" Sidebar): Build a Livewire sidebar component attached to the Ticket Detail view.

Include "Run Triage" and "Generate Reply" buttons.

Implement wire:poll.2s to ping the ai_runs table. Provide a sleek progress UX updating dynamically: "Queued" -> "Retrieving Context..." -> "Drafting..." -> "Ready".

Once "Ready", show the generated draft with an "Apply" button to inject it into the reply text area.

Phase 7: SLA Scheduler, Notifications & QA

Trigger Prompt: "Execute Phase 7: SLA Scheduler, Notifications & QA"

Architectural Intent: Close the loop by ensuring deadlines are met and the system is rigorously tested without incurring API costs.

Scheduler: Create CheckOverdueTargetsJob. Logic: Query tickets where status != Resolved. Compare created_at against the SLA config. If overdue, dispatch an OverdueNotification. Register this job in routes/console.php to run ->hourly().

Notifications: Scaffold standard Laravel Notifications (Database and Mail channels). Create events/notifications for: TicketAssigned, NewTicketReply, and TicketOverdue.

QA & Mocking:

Generate Pest Feature tests covering the core logic and Livewire components.

Crucial: You MUST use AI::fake() in the test suite to ensure the Groq API is never actually hit during CI/CD runs. Mock the expected JSON structures for TriageResult and ReplyDraftResult.

Ensure PHPStan reports 0 errors and type coverage metrics are maxed out.