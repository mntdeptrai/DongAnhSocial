import { useState, useRef, useEffect } from "react";
import { Phone, Video, Minus, X, Image, Mic, ThumbsUp, Send } from "lucide-react";

interface Message {
  id: number;
  text: string;
  sent: boolean;
  time: string;
}

const initialMessages: Message[] = [
  { id: 1, text: "Bạn có khoẻ không?", sent: true, time: "10:30" },
  { id: 2, text: "Mình đang bận một chút.", sent: false, time: "10:31" },
  { id: 3, text: "Ừ, để mình xong việc đã nhé....", sent: false, time: "10:31" },
  { id: 4, text: "lô", sent: true, time: "10:32" },
];

export default function App() {
  const [messages, setMessages] = useState<Message[]>(initialMessages);
  const [input, setInput] = useState("");
  const [isOpen, setIsOpen] = useState(true);
  const [isMinimized, setIsMinimized] = useState(false);
  const bottomRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!isMinimized) {
      bottomRef.current?.scrollIntoView({ behavior: "smooth" });
    }
  }, [messages, isMinimized]);

  const sendMessage = () => {
    const trimmed = input.trim();
    if (!trimmed) return;
    const now = new Date();
    const time = `${now.getHours()}:${String(now.getMinutes()).padStart(2, "0")}`;
    setMessages((prev) => [...prev, { id: Date.now(), text: trimmed, sent: true, time }]);
    setInput("");
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  };

  if (!isOpen) {
    return (
      <div className="fixed bottom-6 right-6 flex flex-col items-end gap-2">
        <button
          onClick={() => setIsOpen(true)}
          className="w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-2xl"
          style={{ background: "linear-gradient(135deg, #0099ff, #0066cc)" }}
          title="Mở chat"
        >
          💬
        </button>
      </div>
    );
  }

  return (
    <div className="fixed bottom-6 right-6 flex flex-col items-end">
      {/* Chat Window */}
      <div
        className="rounded-2xl overflow-hidden flex flex-col shadow-2xl"
        style={{
          width: 328,
          height: isMinimized ? 52 : 520,
          transition: "height 0.25s cubic-bezier(0.4,0,0.2,1)",
          background: "#e8f4fd",
          border: "1px solid rgba(0,0,0,0.08)",
        }}
      >
        {/* Header */}
        <div
          className="flex items-center gap-2.5 px-3 py-2.5 shrink-0"
          style={{ background: "#ffffff", borderBottom: "1px solid rgba(0,0,0,0.07)" }}
        >
          {/* Avatar */}
          <div
            className="w-8 h-8 rounded-full flex items-center justify-center text-lg shrink-0"
            style={{ background: "#fff3cd" }}
          >
            🧑
          </div>

          {/* Name + status */}
          <div className="flex-1 min-w-0">
            <p className="text-sm font-semibold text-gray-900 truncate leading-tight">
              Thành viên Đông A...
            </p>
            <p className="text-xs text-gray-500 leading-tight">Ngoại tuyến</p>
          </div>

          {/* Actions */}
          <div className="flex items-center gap-0.5">
            <button className="w-8 h-8 rounded-full flex items-center justify-center text-blue-500 hover:bg-blue-50 transition-colors">
              <Phone size={15} />
            </button>
            <button className="w-8 h-8 rounded-full flex items-center justify-center text-blue-500 hover:bg-blue-50 transition-colors">
              <Video size={15} />
            </button>
            <button
              onClick={() => setIsMinimized((v) => !v)}
              className="w-8 h-8 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors"
            >
              <Minus size={15} />
            </button>
            <button
              onClick={() => setIsOpen(false)}
              className="w-8 h-8 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors"
            >
              <X size={15} />
            </button>
          </div>
        </div>

        {!isMinimized && (
          <>
            {/* Messages */}
            <div
              className="flex-1 overflow-y-auto px-3 py-3 flex flex-col gap-1.5"
              style={{ scrollbarWidth: "none" }}
            >
              {messages.map((msg, i) => {
                const prevSent = messages[i - 1]?.sent;
                const showAvatar = !msg.sent && (i === 0 || prevSent !== msg.sent || prevSent === true);

                return (
                  <div
                    key={msg.id}
                    className={`flex items-end gap-1.5 ${msg.sent ? "flex-row-reverse" : "flex-row"}`}
                  >
                    {/* Avatar placeholder for spacing */}
                    {!msg.sent && (
                      <div className="w-6 h-6 shrink-0 flex items-end">
                        {showAvatar && (
                          <div
                            className="w-6 h-6 rounded-full flex items-center justify-center text-sm"
                            style={{ background: "#fff3cd" }}
                          >
                            🧑
                          </div>
                        )}
                      </div>
                    )}

                    <div
                      className="max-w-[200px] px-3 py-2 rounded-2xl text-sm leading-snug select-text"
                      style={
                        msg.sent
                          ? {
                              background: "#0084ff",
                              color: "#ffffff",
                              borderBottomRightRadius: 6,
                            }
                          : {
                              background: "#e4e6eb",
                              color: "#050505",
                              borderBottomLeftRadius: 6,
                            }
                      }
                    >
                      {msg.text}
                    </div>
                  </div>
                );
              })}
              <div ref={bottomRef} />
            </div>

            {/* Input bar */}
            <div
              className="shrink-0 flex items-center gap-1.5 px-2 py-2"
              style={{ background: "#ffffff", borderTop: "1px solid rgba(0,0,0,0.07)" }}
            >
              <button className="w-8 h-8 rounded-full flex items-center justify-center text-blue-500 hover:bg-blue-50 transition-colors shrink-0">
                <Mic size={17} />
              </button>
              <button className="w-8 h-8 rounded-full flex items-center justify-center text-blue-500 hover:bg-blue-50 transition-colors shrink-0">
                <Image size={17} />
              </button>

              <div
                className="flex-1 flex items-center rounded-full px-3 py-1.5"
                style={{ background: "#f0f2f5" }}
              >
                <input
                  value={input}
                  onChange={(e) => setInput(e.target.value)}
                  onKeyDown={handleKeyDown}
                  placeholder="Aa"
                  className="flex-1 bg-transparent text-sm outline-none text-gray-900 placeholder-gray-400"
                />
              </div>

              {input.trim() ? (
                <button
                  onClick={sendMessage}
                  className="w-8 h-8 rounded-full flex items-center justify-center text-blue-500 hover:bg-blue-50 transition-colors shrink-0"
                >
                  <Send size={17} />
                </button>
              ) : (
                <button className="w-8 h-8 rounded-full flex items-center justify-center text-blue-500 hover:bg-blue-50 transition-colors shrink-0">
                  <ThumbsUp size={17} />
                </button>
              )}
            </div>
          </>
        )}
      </div>
    </div>
  );
}
